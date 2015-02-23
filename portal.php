<?php
define('ACTION_OK', 1);
define('ACTION_FAILED', 2);
define('SERVER_ERROR', 3);

define('FORM_DATA_MISSING', 4);
define('PASSWORDS_NOT_MATCH', 5);

define('INVALID_ID', 6);

define('EMPTY_BASKET', 7);
define('LOGIN_REQUIRED', 8);

include 'mydb.php';
include 'registration.php';
include 'basket.php';

class Portal
{
  private $dbo = null;
  function initDB($host, $user, $pass, $db)
  {
    $this->dbo = new MyDB($host, $user, $pass, $db);
    $this->dbo->set_charset('utf8');
    if($this->dbo->connect_errno){
      $msg = "Brak połączenia z bazą danych: ";
      $msg .= $this->dbo->connect_error;
      throw new Exception($msg);
    }
  }
  function getUserInfo()
  {
    if(isset($_SESSION['zalogowany'])){
      $str = "Jesteś zalogowany jako: $_SESSION[zalogowany]. ";
      $str .= "<a href=\"logout.php\">Wylogowanie</a>";
    }
    else{
      $str = "Nie jesteś zalogowany.<br />";
      $str .= "<a href=\"index.php?action=showLoginForm\">Logowanie</a>";
    }
    return $str;
  }
  function login()
  {
    if(!$this->dbo) return SERVER_ERROR;
    
    //Sprawdzenie czy zostały przekazane parametry.
    if(!isset($_POST["email"]) || !isset($_POST["haslo"])){
      return ACTION_FAILED;
    }
    
    $email = $_POST["email"];
    $pass = $_POST["haslo"];
    
    //Sprawdzenie długości przekazanych ciągów.
    //Dla kodowania jednobajtowego
    //$userNameLength = strlen($user);
    //$userPassLength = strlen($pass);
    //Dla kodowania utf-8
    $userEmailLength = strlen(utf8_decode($email));
    $userPassLength = strlen(utf8_decode($pass));
    
    if($userEmailLength < 6 || $userEmailLength > 200 ||
     $userPassLength < 6 || $userPassLength > 40){
      return ACTION_FAILED;
    }
  
    //Zabezpieczenie znaków specjalnych w parametrach.
    $email = $this->dbo->real_escape_string($email);
    $pass = $this->dbo->real_escape_string($pass);
    
    //Wykonanie zapytania sprawdzającego poprawność danych.
    $query = "SELECT `Id`, `Imię`, `Nazwisko`, `Hasło` ";
    $query .= "FROM Klienci WHERE `Email`='$email'";

    if(!$result = $this->dbo->query($query)){
      //echo 'Wystąpił błąd: nieprawidłowe zapytanie...';
      return SERVER_ERROR;
    }

    //Sprawdzenie wyników zapytania.
    if($result->num_rows <> 1){
      //Brak użytkownika o wskazanej nazwie lub zbyt wiele wyników.
      return ACTION_FAILED;
    }
    else{
      $row = $result->fetch_row();
      $pass_db = $row[3];
      //Wersja bez kodowania haseł.
      //if($pass != $pass_db){
      //Wersja z kodowaniem haseł.
      if(crypt($pass, $pass_db) != $pass_db){
        return ACTION_FAILED;
      }
      else{
        $_SESSION['zalogowany'] = $row[1].' '.$row[2];
        $_SESSION['userId'] = $row[0];
        return ACTION_OK;
      }
    }
  }
  function logout()
  {
    if(isset($_SESSION['zalogowany'])){
      unset($_SESSION['zalogowany']);
      unset($_SESSION['userId']);
      /*
      //Jeśli sesja ma być usunięta:
      if (isset($_COOKIE[session_name()])){
        setcookie(session_name(), '', time() - 3600);
      }
      session_destroy();
      */
    }
  }
  function showRegistrationForm()
  {
    $reg = new Registration($this->dbo);
    $reg->showRegistrationForm();
  }
  function registerUser()
  {
    $reg = new Registration($this->dbo);
    return $reg->registerUser();
  }
  function showSearchResult()
  {
    //Określenie warunku dla autora.
    if(isset($_GET['autor']) && $_GET['autor'] != ''){
      //Tu lub po przefiltrowaniu dodatkowa weryfikacja poprawności parametru.
      $autor = filter_input(INPUT_GET, 'autor', 
                            FILTER_SANITIZE_SPECIAL_CHARS);
      $cond1 = " AND Autorzy.`Nazwa` LIKE '%$autor%' ";
    }
    else{
      $cond1 = '';
    }
    
    //Określenie warunku dla tytułu.
    if(isset($_GET['tytul']) && $_GET['tytul'] != ''){
      //Tu lub po przefiltrowaniu dodatkowa weryfikacja poprawności parametru.
      $tytul = filter_input(INPUT_GET, 'tytul', 
                            FILTER_SANITIZE_SPECIAL_CHARS);
      $cond2 = " AND Filmy.`Tytuł` LIKE '%$tytul%' ";
    }
    else{
      $cond2 = '';
    }
    
    //Formowanie zapytania.
    $query  = 'SELECT Tytuł, GROUP_CONCAT(Autorzy.Nazwa) AS Autor, ';
    $query .= 'idf, Wytwornie.Nazwa AS Wytwornia, Cena, ';
    $query .= 'Filmy.Id AS Id ';
    $query .= 'FROM Filmy, Wytwornie, Autorzy, FilmyAutorzy ';
    $query .= 'WHERE Filmy.WydawnictwoId = Wytwornie.Id AND ';
    $query .= 'FilmyAutorzy.`FilmId` = Filmy.Id AND ';
    $query .= 'FilmyAutorzy.AutorId = Autorzy.Id ';
    $query .= $cond1.$cond2;
    $query .= 'GROUP BY Filmy.Id';
    
    //Wyświetlenie rezultatów wyszukiwania.
    echo '<div id="searchResultsDiv">';
    if($result = $this->dbo->query($query)){
      if($result->num_rows > 0){
        echo '<table>';
        echo '<tr><th>Tytuł</th><th>Autor</th><th>idf</th>';
        echo '<th>Wytwornia</th><th>Cena</th><th>Koszyk</th></tr>';
        
        //Pętla odczytująca wyniki.
        while($row = $result->fetch_row()){
          echo '<tr>';
          $count = count($row);
          
          //Komórka z tytułem.
          echo '<td><a href="index.php?action=showFilmDetails&amp;id=';
          echo $row[$count - 1];
          echo '">'.$row[0].'</a></td>';
          
          //Pętla odczytująca kolumny wynikowe.
          for($i = 1; $i < $count - 1; $i++){
            echo '<td>'.$row[$i].'</td>';
          }
          
          //Komórka z odnośnikiem do koszyka.
          echo '<td><a href="index.php?action=addToBasket&amp;id=';
          echo $row[$count - 1];
          echo '">Dodaj</a></td>';
          echo '</tr>';
        }
        echo '</table>';
      }
      else{
        echo 'Brak filmow spełniających podane kryteria.';
      }
    }
    else{
      echo 'Wyniki wyszukiwania nie są obecnie dostępne.';
    }
    echo '</div>';
  }
  function showFilmDetails()
  {
    echo '<div id="filmDetailsDiv">';
    
    //Sprawdzenie poprawności identyfikatora filmu (parametr id).
    if(!isset($_GET['id'])){
      echo 'Nieprawidłowy identyfikator filmu.';
      echo '</div>';
      return;
    }
    if(($id = intval($_GET['id'])) < 1){
      echo 'Nieprawidłowy identyfikator filmu.';
      echo '</div>';
      return;
    }
    
    //Formowanie zapytania.
    $query  = 'SELECT `Tytuł`, `idf`, `Rok wydania` AS Rok, `Opis`, ';
    $query .= '`Cena`, Wytwornia.`Nazwa` AS Wytwornia, GROUP_CONCAT(';
    $query .= 'Autorzy.`Nazwa`) AS Autor, Film.Id AS Id ';
    $query .= 'FROM Filmy, Autorzy, Wytwornie, FilmyAutorzy ';
    $query .= 'WHERE Filmy.Id='.$id.' AND Wytwornie.Id=Filmy.WytworniaId ';
    $query .= 'AND Filmy.Id=FilmyAutorzy.`FilmId` AND ';
    $query .= 'Autorzy.Id = FilmyAutorzy.AutorId ';
    $query .= 'GROUP BY Filmy.Id ';

    //Umieszczenie wyników zapytania w tabeli.
    if($result = $this->dbo->query($query)){
      if($row = $result->fetch_assoc()){
        echo '<table>';
        echo '<tr><td width="20%">Tytuł</td><td>'.$row['Tytuł'].'</td>';
        echo '<td rowspan="7" class="textMiddle">';
        echo '<a href="index.php?action=addToBasket&id='.$row['Id'].'">Do koszyka</a></td></tr>';
        echo '<tr><td>Autor</td><td>'.$row['Autor'].'</td></tr>';
        echo '<tr><td>idf</td><td>'.$row['ISBN'].'</td></tr>';
        echo '<tr><td>Wytwornia</td><td>'.$row['Wytwornia'].'</td></tr>';
        echo '<tr><td>Rok wydania</td><td>'.$row['Rok'].'</td></tr>';
        echo '<tr><td>Cena</td><td>'.$row['Cena'].'</td></tr>';
        echo '<tr><td>Opis</td><td>'.$row['Opis'].'</td></tr>';
        echo '</table>';
      }
      else{
        echo 'Brak filmu o podanym identyfikatorze.';
      }
    }    
    else{
      echo 'Błąd serwera. Dane szczegółowe nie są dostępne.';
    }
    echo '</div>';
  }
  function addToBasket()
  {
    $basket = new Basket($this->dbo);
    return $basket->add();
  }
  function showBasket()
  {
    $basket = new Basket($this->dbo);
    $basket->show('Zawartość koszyka', true);
  }
  function modifyBasket()
  {
    $basket = new Basket($this->dbo);
    $basket->modify();
  }
  function checkout()
  {
    if(isset($_SESSION['zalogowany'])){
      $basket = new Basket($this->dbo);
      $basket->show('Podsumowanie zamówienia', false);
    }
    else{
      include 'orderNoLoginInfoDiv.php';
    }
  }
  function saveOrder()
  {
    echo '<div id="basketDiv">';
    
    $basket = new Basket($this->dbo);
    $id = 0;
    
    switch($basket->saveOrder($id)){
      case ACTION_OK:
        echo '<p>Zamówienie zostało złożone.</p>';
        echo "<p>Identyfikator zamówienia: $id.</p>";
        break;
      case EMPTY_BASKET:
        echo '<p>Koszyk jest pusty.</p>';
      break;
      case LOGIN_REQUIRED:
        include 'orderNoLoginInfoDiv.php';
      break;
      case SERVER_ERROR:
      default:
        echo 'Błąd serwera. Zamówienie nie zostało zapisane.';
      break;
    }
    
    echo '</div>';
  }
}
?>
