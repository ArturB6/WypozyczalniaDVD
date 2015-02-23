<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="style.css">
<title>Portal</title>
</head>
<body>
<div id="topDiv">
<div id="headerMainDiv">
<a href="index.php">Główna</a> |
<a href="index.php?action=showSearchForm">Szukaj filmu</a> |
<a href="index.php?action=showBasket">Twój koszyk</a> |
<a href="index.php?action=showRegistrationForm">Rejestracja</a>
</div>
<div id="headerUserInfoDiv">
<?php echo $portal->getUserInfo(); ?>
</div>
<div id="mainContentDiv">

<?php if(isset($komunikat)): ?>
<div id="komunikatDiv"><?php echo $komunikat; ?></div>
<?php endif; ?>

<?php
switch($action):
  case 'showLoginForm' :
    include 'loginForm.php';
    break;
  case 'showSearchForm' :
    include 'searchForm.php';
    break;
  case 'showRegistrationForm':
    $portal->showRegistrationForm();
    break;
  case 'searchFilm':
    include 'searchForm.php';
    $portal->showSearchResult();
    break;
  case 'showFilmDetails':
    $portal->showFilmDetails();
   break;
  case 'showBasket':
    $portal->showBasket();
    break;
  case 'checkout':
    $portal->checkout();
    break;
  case 'saveOrder':
    $portal->saveOrder();
    break;
  case 'showMain':
  default:
    include 'mainContents.php';
endswitch;
?>
</div>
<div id="footerDiv">
Stopka strony
</div>
</div>
</body>
</html>
