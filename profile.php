<?php
session_start();

require_once("vendor/autoload.php");

$user = new Users();
$order = new Orders();
$token = new Tokens();
$update_errors_arr = [];

if (isset($_SESSION["user_id"])) {
    // if user have a cookie
    if (isset($_COOKIE["hashed_token"])) {
        // Updating the cookie.
        $profile_user_id = $token->get_user_id_using_hashed_token($_COOKIE["hashed_token"]);
        $new_hashed_token = Utility::randomkey(127);
        $token->update_token($profile_user_id, $new_hashed_token);
        setcookie("hashed_token", $new_hashed_token, time() + (10 * 365 * 24 * 60 * 60));
    }

    if (isset($_POST["submit"])) {
        //Email variables
        $old_email = $user->show_user_email($_SESSION["user_id"]);

        // Password variables
        $database_old_password = $user->show_user_password($_SESSION["user_id"]);
        $input_old_password = !empty($_POST["old_password"])? sha1($_POST["old_password"]): "";
        $input_new_password = !empty($_POST["password"])? sha1($_POST["password"]): "";
        $input_confirm_password = !empty($_POST["confirm_new_password"])? sha1($_POST["confirm_new_password"]): "";

        // Email checks.
        if (!empty($_POST["email"])) {
            validate_user_email($_SESSION["user_id"], $old_email, $_POST["email"]);
        }

        // Password checks.
        // if all fields are empty do nothing.
        if (empty($input_old_password) && empty($input_new_password) && empty($input_confirm_password)) {
            // if one field is empty push an error.
        } else if (empty($input_old_password) || empty($input_new_password) || empty($input_confirm_password)) {
            array_push($update_errors_arr, 'Please fill all password fields');
            // only if all the fields are full validate.
        } else if (!empty($input_old_password) && !empty($input_new_password) && !empty($input_confirm_password)) {
            validate_user_password($_SESSION["user_id"], $database_old_password, $input_old_password, $input_new_password, $input_confirm_password);
        }
    }

    require_once("views/profile.html");

} else {

    header("Location:login.php");
}




// All the functions
function validate_user_email($user_id, $current_email, $new_email) {
    $user = new Users();
    global $update_errors_arr;
    $email_registered = $user->is_registered($_POST["email"]); // True if email is already registered.

    if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        if ($email_registered) {
            if ($new_email == $current_email) {
                array_push($update_errors_arr, 'The new Email matches the old email.');
            } else {
                array_push($update_errors_arr, "Email is already registered with another account, please try with different Email.");
            }
        } else if ($new_email == $current_email) {
        } else {
            $user = new Users();
            $user->updateUserEmail($new_email, $user_id);
        }
    } else {
        array_push($update_errors_arr, 'Email is invalid.');
    }
}



function validate_user_password($user_id, $database_old_password, $input_old_password, $input_new_password, $input_confirm_password) {
    global $update_errors_arr;
    if ($input_old_password == $database_old_password) {
        if (!(trim(preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/", $_POST["password"])))) {
            array_push($update_errors_arr, 'Password must be at least 8 characters in length and must contain at least one number, 
        one upper case letter, one lower case letter and one special character.');
        } elseif ($input_confirm_password != $input_new_password) {
            array_push($update_errors_arr, "Passwords didn't match.");
        } else {
            $user = new Users();
            $user->updateUserPassword($input_new_password, $user_id);
        }
    } else {
        array_push($update_errors_arr, 'Old password is not correct.');
    }
}
