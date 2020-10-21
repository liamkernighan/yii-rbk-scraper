<?php

namespace app\controllers;

use app\helpers\RbkPostsFinder;
use app\models\RbkPosts;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\helpers\StringHelper;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $count = RbkPosts::find()->count();
        $pages = new Pagination([
            'totalCount' => $count,
            'pageSize' => 10,
        ]);



        $models = RbkPosts::find()
            ->orderBy(['date' => SORT_ASC])
            ->offset($pages->offset)
            ->limit(10)
            ->all();


        return $this->render('index', [
            'models' => $models,
            'pages' => $pages,
        ]);
    }


    /**
     * Shows RBK posts
     * @return string
     */
    public function actionPosts()
    {
        return $this->render('posts');
    }

    public function actionScrape()
    {
        $posts = (new RbkPostsFinder)->getArrayOfStructuredPosts();
        $post_hashes = [];
        foreach ($posts as $post) {
            array_push($post_hashes, $post->hash);
        }

        $db_posts = RbkPosts::find()->where(['in', 'hash', $post_hashes])
            ->all();



        foreach ($posts as $post) {

            $filtered_posts = array_filter($db_posts, function ($desired_post) use ($post) {
                return $desired_post->hash === $post->hash;
            });

            $db_post = array_shift($filtered_posts);
            if (!$db_post) {
                $db_post = new RbkPosts();
                $db_post->date = date("Y-m-d H:i:s"); /** @todo выводить юзеру в текущем часовом поясе */
            }

            $db_post->hash = $post->hash;
            $db_post->title = $post->title;
            $db_post->content = $post->content;
            $db_post->picture = $post->img_path;
            $db_post->category = ''; /** @todo Вбить */
            $db_post->save(false);



            return $this->redirect(['index']);
        }
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
