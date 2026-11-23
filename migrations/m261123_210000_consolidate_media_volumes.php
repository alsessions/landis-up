<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\elements\Asset;
use craft\fs\Local;
use craft\helpers\FileHelper;
use RuntimeException;
use yii\db\Expression;

class m261123_210000_consolidate_media_volumes extends Migration
{
    public function safeUp(): bool
    {
        $volumes = Craft::$app->getVolumes();
        $assetsService = Craft::$app->getAssets();
        $sourceVolume = $volumes->getVolumeByHandle('fpImages');
        $targetVolume = $volumes->getVolumeByHandle('images');

        if (!$targetVolume) {
            throw new RuntimeException('The Images volume does not exist.');
        }

        if ($this->db->getTableSchema('{{%media_redirects}}', true) === null) {
            $this->createTable('{{%media_redirects}}', [
                'id' => $this->primaryKey(),
                'oldPath' => $this->string(768)->notNull(),
                'assetId' => $this->integer()->notNull(),
            ]);
            $this->createIndex(null, '{{%media_redirects}}', ['oldPath'], true);
            $this->createIndex(null, '{{%media_redirects}}', ['assetId']);
            $this->addForeignKey(null, '{{%media_redirects}}', ['assetId'], '{{%assets}}', ['id'], 'CASCADE');
        }

        if (!$sourceVolume) {
            echo "The FP Images volume has already been removed.\n";
            return true;
        }

        $assets = Asset::find()
            ->volumeId($sourceVolume->id)
            ->status(null)
            ->orderBy(['elements.id' => SORT_ASC])
            ->all();

        foreach ($assets as $index => $asset) {
            $oldPath = ltrim($asset->getPath(), '/');
            $owner = $this->ownerForAsset($asset->id);
            $folderPath = $this->folderPath($owner);
            $folder = $assetsService->ensureFolderByFullPathAndVolume($folderPath, $targetVolume, false);

            $asset->avoidFilenameConflicts = true;
            if (!$assetsService->moveAsset($asset, $folder)) {
                throw new RuntimeException(sprintf(
                    'Unable to move asset %d (%s): %s',
                    $asset->id,
                    $oldPath,
                    implode('; ', $asset->getErrorSummary(true)),
                ));
            }

            $this->db->createCommand()->upsert('{{%media_redirects}}', [
                'oldPath' => $oldPath,
                'assetId' => $asset->id,
            ], [
                'assetId' => $asset->id,
            ])->execute();

            if (($index + 1) % 50 === 0 || $index + 1 === count($assets)) {
                echo sprintf("Moved %d of %d assets.\n", $index + 1, count($assets));
            }
        }

        $fs = $sourceVolume->getFs();
        if ($fs instanceof Local && basename($fs->getRootPath()) === 'fpimages') {
            FileHelper::removeDirectory($fs->getRootPath());
            echo "Removed the obsolete FP Images transform directory.\n";
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "This migration cannot be reverted automatically.\n";
        return false;
    }

    private function ownerForAsset(int $assetId): ?array
    {
        return (new Query())
            ->select([
                'fieldHandle' => 'f.handle',
                'sectionHandle' => 's.handle',
                'slug' => 'es.slug',
            ])
            ->from(['r' => '{{%relations}}'])
            ->innerJoin(['f' => '{{%fields}}'], '[[f.id]] = [[r.fieldId]]')
            ->innerJoin(['sourceEntry' => '{{%entries}}'], '[[sourceEntry.id]] = [[r.sourceId]]')
            ->innerJoin(['sourceElement' => '{{%elements}}'], '[[sourceElement.id]] = [[sourceEntry.id]]')
            ->innerJoin(['ownerEntry' => '{{%entries}}'], '[[ownerEntry.id]] = COALESCE([[sourceEntry.primaryOwnerId]], [[sourceEntry.id]])')
            ->innerJoin(['ownerElement' => '{{%elements}}'], '[[ownerElement.id]] = [[ownerEntry.id]]')
            ->leftJoin(['s' => '{{%sections}}'], '[[s.id]] = [[ownerEntry.sectionId]]')
            ->innerJoin(['site' => '{{%sites}}'], '[[site.primary]] = 1')
            ->innerJoin(['es' => '{{%elements_sites}}'], '[[es.elementId]] = [[ownerEntry.id]] AND [[es.siteId]] = [[site.id]]')
            ->where([
                'r.targetId' => $assetId,
                'sourceElement.dateDeleted' => null,
                'sourceElement.canonicalId' => null,
                'sourceElement.draftId' => null,
                'sourceElement.revisionId' => null,
                'ownerElement.dateDeleted' => null,
                'ownerElement.canonicalId' => null,
                'ownerElement.draftId' => null,
                'ownerElement.revisionId' => null,
            ])
            ->orderBy(new Expression(<<<SQL
CASE [[s.handle]]
    WHEN 'news' THEN 1
    WHEN 'events' THEN 2
    WHEN 'page' THEN 3
    WHEN 'collections' THEN 4
    WHEN 'gallery' THEN 5
    WHEN 'homePage' THEN 6
    ELSE 7
END, [[ownerEntry.id]]
SQL))
            ->one() ?: null;
    }

    private function folderPath(?array $owner): string
    {
        if (!$owner) {
            return 'shared';
        }

        if ($owner['fieldHandle'] === 'featuredImage') {
            return 'featured';
        }

        $section = match ($owner['sectionHandle']) {
            'page' => 'pages',
            'homePage' => 'homepage',
            default => $owner['sectionHandle'] ?: 'shared',
        };

        return $owner['slug'] ? "$section/{$owner['slug']}" : $section;
    }
}
