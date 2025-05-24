<?php

namespace Modules\Authentication\Foundation;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;

trait Authentication
{
    public static function authentication($credentials, $loginBy = 'email_and_mobile')
    {
        $auth = null;

        if ($loginBy == 'email_and_mobile') {
            $auth = self::loginByEmailAndMobile($credentials);
        } elseif ($loginBy == 'mobile_only') {
            $auth = self::loginByMobile($credentials);
        } elseif ($loginBy == 'email_only') {
            $auth = self::loginByEmail($credentials);
        }

        return $auth;
    }

    private static function loginByMobile($credentials)
    {
        $auth = null;
        if (is_numeric($credentials->email)) {
            $data = [
                'calling_code' => $credentials->calling_code ?? '965',
                'mobile' => $credentials->email,
                'password' => $credentials->password,
            ];
            /* if (config("app.have_sms") == true) {
            $data['is_verified'] = 1;
            } */
            $auth = Auth::attempt($data, $credentials->has('remember'));
        }
        return $auth;
    }

    private static function loginByEmail($credentials)
    {
        $auth = null;
        if (filter_var($credentials->email, FILTER_VALIDATE_EMAIL)) {
            $auth = Auth::attempt(
                [
                    'email' => $credentials->email,
                    'password' => $credentials->password,
                ],
                $credentials->has('remember')
            );
        }
        return $auth;
    }

    private static function loginByEmailAndMobile($credentials)
    {
        $auth = null;
        if (filter_var($credentials->email, FILTER_VALIDATE_EMAIL)) {
            $auth = Auth::attempt(
                [
                    'email' => $credentials->email,
                    'password' => $credentials->password,
                ],
                $credentials->has('remember')
            );
        } elseif (is_numeric($credentials->email)) {
            $data = [
                'calling_code' => $credentials->calling_code ?? '965',
                'mobile' => $credentials->email,
                'password' => $credentials->password,
            ];
            /* if (config("app.have_sms") == true) {
            $data['is_verified'] = 1;
            } */
            $auth = Auth::attempt($data, $credentials->has('remember'));
        }

        return $auth;
    }

    public function login($credentials, $loginBy = 'email_and_mobile')
    {
        try {
            if (self::authentication($credentials, $loginBy)) {
                return false;
            }

            $errors = new MessageBag([
                'password' => __('authentication::dashboard.login.validations.failed'),
            ]);

            return $errors;
        } catch (Exception $e) {

            return $e;
        }
    }

    public function loginAfterRegister($credentials)
    {
        try {
            self::authentication($credentials);
        } catch (Exception $e) {
            return $e;
        }
    }

    public function generateToken($user)
    {
        $tokenResult = $user->createToken('Personal Access Token');

        $token = $tokenResult->token;

        $token->save();

        return $tokenResult;
    }

    public function tokenExpiresAt($token)
    {
        return Carbon::parse($token->token->expires_at)->toDateTimeString();
    }
}
