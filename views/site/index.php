<?php

/* @var $this yii\web\View */

use yii\helpers\StringHelper;
use yii\widgets\LinkPager;

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <div class="jumbotron">
        <form method="post" action="site/scrape">
            <button type="submit" class="btn btn-primary">Scrape RBK</button>
            <input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" />
        </form>
    </div>

    <div class="body-content text-center">

        <?php foreach ($models as $model): ?>

            <div>
                <h5><?= $model->title ?></h5>
                <div><?= $model->preview ?></div>
                <?php if (strlen($model->picture) > 0): ?>
                    <img src="<?=$model->picture?>" width="500">
                <?php endif ?>

            </div>

        <?php endforeach ?>


        <?= LinkPager::widget([
                'pagination' => $pages,
        ]) ?>

    </div>
</div>
