<?php
namespace app\controllers;
use Yii;
use app\models\User;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBasicAuth;
use  yii\base\DynamicModel;
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
        $password=$request->post('password');
        $username=$request->post('username');
        return $this->check('signup',compact('username','password'))? $this->createUser($password,$username):
            ['error'=>'username or password can\'t be empty,and password\'s length must be greater than 6,and username\' length must be between 3 and 20.'];
    }
    public function actionSignin(){
        $request = Yii::$app->request;
        $password=$request->post('password');
        $username=$request->post('username');
        return $this->check('signin',compact('username','password'))?
            User::auth($username,$password):
            ['error'=>'Username or password can\'t be empty,and password\'s length must be greater than 6,and username\' length must be between 3 and 20.'];
    }
    public function actionChangepassword(){
        $request = Yii::$app->request;
        $password=$request->post('password');
        $secret=$request->post('token');
          return $this->check('changePassword',compact('secret','password'))?
              $this->reset($request->post('token'),compact('password')):
              ['error'=>'token or new password can\'t be empty,and password\'s length must be greater than 6.'];
    }
   public function createUser($password,$username){
       $customer=User::create($username,$password);
       return ['message'=>'sign up successfully!!!','token'=>$customer->token,'username'=>$customer->username];
   }
    public function check($type,$fields){
        $model = new DynamicModel($fields);
        if($type==='signup'||$type==='signin') {
            $model->addRule(['username', 'password'], 'required')
                ->addRule('username', 'string', ['length' => [3, 20]])
                ->addRule('password', 'string', ['length' => [6]])
                ->validate();
        }else if($type==='changePassword'){
            $model->addRule(['secret', 'password'], 'required')
                ->addRule('password', 'string', ['length' => [6]])
                ->validate();
        }
       /* forEach($fields as $field){
           if( !$field){
               return false;
           }
        }*/
       return $model->hasErrors()? false:true;
    }
   public function reset($token,$fields){
       $user=User::findIdentityByAccessToken($token);
       $resps=[];
       foreach($fields as $index=>$field){
           $func='reset'.ucfirst($index);
           $resps[$index]=$this->$func($user,$field);
       }
      return isset($user)?$resps : ['error'=>'Invalid token'];

   }
    public function resetPassword($user,$password){
        $user->resetPassword($password);
        return ['message'=>'change password successfully!!!','new password'=>$password];
    }
}