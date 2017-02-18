<?php
namespace app\controllers;
use Yii;
use app\models\User;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBasicAuth;

class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';
 /*   public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBasicAuth::className(),
        ];
        return $behaviors;
    }*/



    public function actions()
    {
        $actions = parent::actions();

        // disable the "delete" and "create" actions
        unset($actions['delete'], $actions['create'],$actions['index'],$actions['update'],
            $actions['options'],$actions['view']);


        return $actions;
    }
    public function actionSignup(){
        $request = Yii::$app->request;

        if($this->check($request,['username','password'])){
           $customer=User::create($request->post('username'),$request->post('password'));
            return ['message'=>'sign up successfully!!!','token'=>$customer->token,'username'=>$customer->username];
        }

        return ['error'=>'username or password can\'t be empty'];
    }
    public function actionSignin(){
        $request = Yii::$app->request;
        if($this->check($request,['username','password'])){
           return User::auth($request->post('username'),$request->post('password'));

        }
        return ['error'=>'Username or password can\'t be empty'];
    }
    public function actionChangepassword(){
        $request = Yii::$app->request;
          if($this->check($request,['token','password'])){
              if($user=User::findIdentityByAccessToken($request->post('token'))){
                  $user->resetPassword($request->post('password'));
                  return ['message'=>'change password successfully!!!','new password'=>$request->post('password')];
              }
              return ['error'=>'Invalid token'];
          }
        return ['error'=>'token or new password can\'t be empty'];
    }

    public function check($request,$fields){
        forEach($fields as $field){
           if( !$request->post($field)){
               return false;
           }
        }
        return true;
    }

}