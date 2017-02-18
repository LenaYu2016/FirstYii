<?php

namespace app\models;
use yii\db\ActiveRecord;
use Yii;
class User extends ActiveRecord implements \yii\web\IdentityInterface
{
    public function validateAuthKey($authKey)
    {
        // TODO: Implement validateAuthKey() method.
    }
    public function getAuthKey()
    {
        // TODO: Implement getAuthKey() method.
    }

    public static function tableName()
    {
        return '{{users}}';
    }
    public static function primaryKey()
    {
        return ['id'];
    }
    public function rules()
    {
        return [
            [['username', 'password', 'token'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return self::findOne($id) ? new static(self::findOne($id)) : null;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        foreach (self::find()->all() as $user) {
            if ($user->token === $token) {
                return $user;
            }
        }

        return null;
    }
    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        foreach (self::find()->all() as $user) {
            if (strcasecmp($user->username, $username) === 0) {
                return $user;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }
    public static function create($username,$psd){
        $customer = new User();
        $customer->username =  $username;
        $customer->password = Yii::$app->getSecurity()->generatePasswordHash($psd);
        $customer->token=Yii::$app->getSecurity()->generateRandomString(20);
        $customer->save(false);
        return $customer;
    }
    public static function auth($username,$password){
        if($user=self::findByUsername($username)){
            if($user->password===Yii::$app->getSecurity()->generatePasswordHash($password)){
                $user->token=Yii::$app->getSecurity()->generateRandomString(20);
                $user->save();
                return ['message'=>'sign in success!!!','token'=>$user->token,'username'=>$user->username];
            }
            return ['error'=>'Invalid password.'];
        }
        return ['error'=>'Username doesn\'t exist.Please sign up first.'];
    }
    public function resetPassword($password){
        $this->password=Yii::$app->getSecurity()->generatePasswordHash($password);
        $this->save();
    }
}
