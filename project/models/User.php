<?php

namespace Project\Models;

use \Core\Model;

class User extends Model
{

    // Метод добавления нового пользователя
    public function register($name, $email, $password, $country)
    {
        $sql = 'INSERT INTO user (name, email, password, country) VALUES (:name, :email, :password, :country)';

        $rezult = self::$link->prepare($sql);
        $rezult->bindParam(':name', $name, \PDO::PARAM_STR);
        $rezult->bindParam(':email', $email, \PDO::PARAM_STR);
        $rezult->bindParam(':password', $password, \PDO::PARAM_STR);
        $rezult->bindParam(':country', $country, \PDO::PARAM_STR);

        return $rezult->execute();
    }

    // Метод проверки на существование
    public function checkExist($email)
    {
        $sql = 'SELECT COUNT(*) FROM user WHERE email = :email';
        $rezult = self::$link->prepare($sql);
        $rezult->bindParam(':email', $email, \PDO::PARAM_STR);
        $rezult->execute();
        if ($rezult->fetchColumn()) {
            return false;
        }
        return true;
    }

    // Метод проверки логина
    public function checkName($name)
    {
        $pattern = '/^[a-zA-Z][a-zA-Z0-9-_\.]{1,20}$/';

        if (isset($name) && strlen($name) > 6) {
            if (preg_match($pattern, $name)) {
                return true;
            }
        }
        return false;
    }

    // Метод проверки почты
    public function checkEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        return false;
    }

    // Метод проверки пароля на валидность
    public function checkPass($password)
    {
        if (strlen($password) >= 6) {
            return true;
        }
        return false;
    }

    // Метод поиска введенной пары логин-пароль в базе
    public function checkUser($email, $pass)
    {
        $sql = 'SELECT * FROM user WHERE email = :email and password = :pass';
        $rezult = self::$link->prepare($sql);
        $rezult->bindParam(':email', $email, \PDO::PARAM_STR);
        $rezult->bindParam(':pass', $pass, \PDO::PARAM_STR);
        $rezult->execute();

        $user = $rezult->fetch();

        if ($user) {
            return $user['user_id'];
        } else {
            return false;
        }
    }

    // Метод проверки авторизован пользователь? Возвращает id пользователя
    public function checkLogged()
    {
        if (isset($_SESSION['id'])) {
            return $_SESSION['id'];
        }
        return false;
    }

    // Метод авторизации пользователя
    public function authUser($userID)
    {
        $userInfoArray = $this->getUserById($userID);

        $_SESSION['name']       = $userInfoArray['name'];
        $_SESSION['id']         = $userInfoArray['user_id'];
        $_SESSION['email']      = $userInfoArray['email'];
        $_SESSION['country']    = $userInfoArray['country'];
    }

    // Метод проверки авторизован пользователь? Возвращает true или false
    public function isGuest()
    {
        if (isset($_SESSION['id'])) {
            return false;
        }
        return true;
    }

    // Метод для выхода из аккаунта
    public function userLogout()
    {
        if (isset($_SESSION['id'])) {
            unset($_SESSION['id']);
        }
        return true;
    }

    // Метод получения данных об авторизованном пользователе
    public function getUserById($userID)
    {
        $sql = 'SELECT * FROM user WHERE user_id = :id';
        $rezult = self::$link->prepare($sql);
        $rezult->bindParam(':id', $userID, \PDO::PARAM_STR);
        $rezult->setFetchMode(\PDO::FETCH_ASSOC);
        $rezult->execute();
        return $rezult->fetch();
    }
}
