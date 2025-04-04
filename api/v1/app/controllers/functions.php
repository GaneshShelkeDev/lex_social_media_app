<?php

function generate_jwt_token($id)
{
    try {//create a jwt session token for this user
        $jwt_token = hash('sha256', $id . time());
         //insert token

        return $jwt_token;
        //catch exception
    } catch (Exception $e) {
        return false;
    }
}
