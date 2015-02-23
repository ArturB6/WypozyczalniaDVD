<?php
session_start();
include "portal.php";

$portal = new Portal();
try{
  $portal->initDB("localhost", "php", "test", "Wypozyczalnia");
}
catch(Exception $e){
  //echo 'Brak połączenia z bazą danych.';
  //exit();
}
if(isset($_GET['action'])){
  $action = $_GET['action'];
}
else{
  $action = 'showMain';
}
switch($action){
  case 'login':
    if(!isset($_SESSION['zalogowany'])){
      switch ($portal->login()):
        case ACTION_OK:
          $action = 'showMain';
          break;
        case ACTION_FAILED:
          $komunikat = "Nieprawidłowa nazwa lub hasło!";
          $action = 'showLoginForm';
          break;
        case SERVER_ERROR:
        default:
          $komunikat = "Błąd serwera!";
          $action = 'showLoginForm';
      endswitch;
    }
    else{
      $komunikat = "Najpierw musisz się wylogować!";
      $action = 'showLoginForm';
    }
    break;
  case 'logout':
    $portal->logout();
    $action = 'showMain';
    break;
  case 'registerUser':
    switch($portal->registerUser()):
      case ACTION_OK:
        $komunikat = 'Rejestracja prawidłowa. Możesz się teraz zalogować.';
        $action = 'showMain';
        break;
      case FORM_DATA_MISSING:
        $komunikat = 'Proszę wypełnić wszystkie pola formularza!';
        $action = 'showRegistrationForm';
        break;
      case ACTION_FAILED:
        $komunikat = 'W tej chwili rejestracja nie jest możliwa.';
        $action = 'showRegistrationForm';
        break;
      case SERVER_ERROR:
        $komunikat = 'Błąd serwera!';
        $action = 'showRegistrationForm';
      default:
    endswitch;
    break;
  case 'addToBasket':
    $action = 'showBasket';
    switch($portal->addToBasket()):
      case INVALID_ID:
      case FORM_DATA_MISSING:
        $komunikat = 'Błędny identyfikator Filmu.';
        break;
      case ACTION_OK:
        $komunikat = 'Film została dodana do koszyka.';
        break;
      default:
        $komunikat = 'Błąd serwera.';
    endswitch;
    break;
  case 'modifyBasket':
    $action = 'showBasket';
    $komunikat = 'Zawartość koszyka została uaktualniona.';
    $portal->modifyBasket();
    break;
}
include 'main.php';
?>
