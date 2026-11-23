<?php

namespace craft\contentmigrations;

use craft\db\Migration;
use craft\db\Query;
use craft\elements\Asset;

class m261123_211000_update_legacy_media_urls extends Migration
{
    public function safeUp(): bool
    {
        if ($this->db->getTableSchema('{{%media_redirects}}', true) === null) {
            echo "The media redirect map does not exist.\n";
            return true;
        }

        $assetsByPath = [];
        $assetsByFilename = [];

        foreach ((new Query())->from('{{%media_redirects}}')->all() as $redirect) {
            $asset = Asset::find()->id($redirect['assetId'])->status(null)->one();
            if (!$asset || !$asset->getUrl()) {
                continue;
            }

            $path = $this->normalizePath($redirect['oldPath']);
            $assetsByPath[$path] = $asset;
            $assetsByFilename[strtolower(basename($path))][] = $asset;
        }

        $rows = (new Query())
            ->select(['es.elementId', 'es.siteId', 'es.content'])
            ->from(['es' => '{{%elements_sites}}'])
            ->innerJoin(['e' => '{{%elements}}'], '[[e.id]] = [[es.elementId]]')
            ->innerJoin(['en' => '{{%entries}}'], '[[en.id]] = [[e.id]]')
            ->where(['e.dateDeleted' => null, 'e.canonicalId' => null])
            ->andWhere(['like', 'es.content', '/assets/fpimages'])
            ->all();

        $updated = 0;

        foreach ($rows as $row) {
            $content = preg_replace_callback(
                '#/assets/fpimages/(?<path>[^}"<\\s]+)#i',
                function(array $match) use ($assetsByPath, $assetsByFilename): string {
                    $path = $this->normalizePath(rawurldecode($match['path']));
                    $asset = $assetsByPath[$path] ?? null;

                    if (!$asset) {
                        $matches = $assetsByFilename[strtolower(basename($path))] ?? [];
                        $asset = count($matches) === 1 ? $matches[0] : null;
                    }

                    return $asset
                        ? parse_url($asset->getUrl(), PHP_URL_PATH)
                        : $match[0];
                },
                $row['content'],
            );

            if ($content !== $row['content']) {
                $this->update('{{%elements_sites}}', ['content' => $content], [
                    'elementId' => $row['elementId'],
                    'siteId' => $row['siteId'],
                ]);
                $updated++;
            }
        }

        echo "Updated $updated entries with current Site Media URLs.\n";
        return true;
    }

    public function safeDown(): bool
    {
        echo "This migration cannot be reverted automatically.\n";
        return false;
    }

    private function normalizePath(string $path): string
    {
        $parts = array_filter(
            explode('/', trim($path, '/')),
            fn(string $part): bool => !str_starts_with($part, '_') && !ctype_digit($part),
        );

        return strtolower(implode('/', $parts));
    }
}
