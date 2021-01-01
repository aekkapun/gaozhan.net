<?php


namespace app\modules\api1\components;


use app\modules\api1\models\User;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;

class Controller extends \yii\rest\Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        if ($this->getCurrentUser() == null) {

            $behaviors['authenticator'] = [
                'class' => CompositeAuth::className(),
                'authMethods' => [
                    'basicAuth' => [
                        'class' => HttpBasicAuth::className(),
                        'auth' => function ($username, $password) {
                            $user = User::find()->where(['username' => $username])->one();
                            if ($user !== null && $user->validatePassword($password)) {
                                return $user;
                            }
                            return null;
                        },
                    ],
                ],
            ];

        }
        return $behaviors;
    }

    /**
     * Returns current user.
     * Should be used in order to support token-less auth via web session that is handy for AJAX.
     *
     * @return null|User
     */
    protected function getCurrentUser()
    {
        /** @var User $user */
        $user = \Yii::$app->getUser()->getIdentity();
        return $user;
    }
}