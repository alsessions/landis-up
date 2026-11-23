<?php

namespace modules\controllers;

use Craft;
use craft\db\Query;
use craft\elements\Asset;
use craft\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class MediaRedirectsController extends Controller
{
    protected array|int|bool $allowAnonymous = ['redirect'];

    public function actionRedirect(string $path): Response
    {
        $table = Craft::$app->getDb()->getTableSchema('{{%media_redirects}}', true);
        if ($table === null) {
            throw new NotFoundHttpException();
        }

        $assetId = (new Query())
            ->select(['assetId'])
            ->from('{{%media_redirects}}')
            ->where(['oldPath' => rawurldecode(ltrim($path, '/'))])
            ->scalar();

        $asset = $assetId
            ? Asset::find()->id($assetId)->status(null)->one()
            : null;

        if (!$asset || !$asset->getUrl()) {
            throw new NotFoundHttpException();
        }

        return $this->redirect($asset->getUrl(), 301);
    }
}
