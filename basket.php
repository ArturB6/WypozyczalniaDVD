<?php
class Basket
{
  private $dbo = null;
  function __construct($dbo)
  {
    $this->dbo = $dbo;
    
    //Utworzenie koszyka, jesli koniczne.
    if(!isset($_SESSION['basket'])){
      $_SESSION['basket'] = array();
    }
  }
  function add()
  {
    //Sprawdzenie poprawności parametru id.
    if(!isset($_GET['id'])){
      return FORM_DATA_MISSING;
    }
    if(($id = intval($_GET['id'])) < 1){
      return INVALID_ID;
    }
    
    //Sprawdzenie czy istnieje film o podanym id.
    $query = "SELECT COUNT(*) FROM Filmy WHERE id=$id";
    if(!$this->dbo->getQuerySingleResult($query)){
      return INVALID_ID;
    }
    
    //Zapisanie identyfikatora filmu w koszyku.
    if(isset($_SESSION['basket'][$id])){
      $_SESSION['basket'][$id]++;
    }
    else{
      $_SESSION['basket'][$id] = 1;
    }
    return ACTION_OK;
  }
  function show($msg, $allowModify = true)
  {
    echo '<div id="basketDiv">';
    if(count($_SESSION['basket']) == 0){
      echo 'Koszyk jest pusty.';
    }
    else{
      //Pobranie listy identyfikatorów dla warunku zapytania.
      $ids = implode(',', array_keys($_SESSION['basket']));
      
      //Pobranie danych dotyczących filmu z koszyka.
      $query  = 'SELECT `Id`, `Tytuł`, `Cena` FROM Filmy ';
      $query .= 'WHERE id IN('.$ids.') ORDER BY `Tytuł`';
      
      if($result = $this->dbo->query($query)){
        echo '<form action="index.php?action=modifyBasket" method="post">';
        echo '<table>';
        
        //Górny wiersz tabeli z kounikatem z $msg.
        echo '<tr><td colspan="4" class="textMiddle">'.$msg.'</td></tr>';
        
        //Nagłówki kolumn.
        echo '<tr><th>Tytuł</th><th class="textRight">Cena</th>';
        echo '<th class="textRight">Liczba</th><th class="textRight">';
        echo 'Wartość</th></tr>';
        
        //Odczytanie wyników zapytania.
        $suma = 0;
        while($row = $result->fetch_row()){
          echo '<tr>';
          echo '<td class="textLeft">'.$row[1].'</td>';
          echo '<td class="textRight">'.$row[2].'</td>';
          
          //Liczba egzemplarzy i wartość dla danej Filmu.
          $ile = $_SESSION['basket'][$row[0]];
          $wartosc = $ile * $row[2];
          $wartosc = sprintf("%01.2f", $wartosc);
          
          //Sumowanie całkowtiej wartości koszyka.
          $suma += $wartosc;
          
          echo '<td class="textRight">';
          if($allowModify){
            //Jeżeli dopuszczamy modyfikację liczby egzemplarzy.
            echo '<input type="text" name="'.$row[0];
            echo '" value="'.$ile.'" size="2" class="textRight" />';
          }
          else{
            //Jeżeli podsumowujemy zamówienie.
            echo $ile;
          }
          echo '</td>';
          echo '<td class="textRight">'.$wartosc.'</td>';
          echo '</tr>';
        }
        
        //Formatowanie sumy zamówienia.
        $suma = sprintf("%01.2f", $suma);
        echo '<tr><td colspan="3">Całkowita wartość</td>';
        echo '<td class="textRight">'.$suma.'</td></tr>';
        
        if($allowModify){
          echo '<tr><td colspan="4" class="textRight"><input type="submit"';
          echo 'value="Zapisz zmiany"></td></tr>';
        }
        
        echo '<tr><td colspan="4" class="textRight">';
        if($allowModify){
          //Odnośnik do podsumowania zamówienia.
          echo '<a href="index.php?action=checkout">Do kasy</a>';
        }
        else{
          //Odnośnik do zapisywania zamówienia w systemie.
          echo '<a href="index.php?action=saveOrder">';
          echo 'Złóż zamówienie</a>';
        }
        echo '</td></tr>';
        echo '</table>';
      }
      else{
        echo 'Błąd serwera. Zawartość koszyka nie jest dostępna.';
      }
    }
    echo '</div>';
  }
  function modify()
  {
    foreach($_SESSION['basket'] as $key => $val){
      if(!isset($_POST[$key])){
        unset($_SESSION['basket'][$key]);
      }
      else if(intval($_POST[$key]) < 1){
        unset($_SESSION['basket'][$key]);
      }
      else{
        $_SESSION['basket'][$key] = intval($_POST[$key]);
      }
    }
  }
  function saveOrder(&$orderId)
  {
    
    //Sprawdzenie czy koszyk ma zawartość.
    if(count($_SESSION['basket']) < 1){
      return EMPTY_BASKET;
    }
    
    //Sprawdzenie czy użytkownik jest zalogowany.
    if(!isset($_SESSION['userId'])){
      return LOGIN_REQUIRED;
    }
    
    //Pobranie identyfikatorów filmów z koszyka.
    $ids = implode(',', array_keys($_SESSION['basket']));
    $userId = $_SESSION['userId'];
    
    //Wyłączenie automatycznego zatwierdzania transakcji.
    $this->dbo->autocommit(false);
    
    //Utworzenie nowego zamówienia.
    $query  = 'INSERT INTO Zamowienia ';
    $query .= "VALUES(0, $userId, NOW(), NULL, 0)";
    
    if(!$this->dbo->query($query)){
      return SERVER_ERROR;
    }
    
    if(($orderId = $this->dbo->insert_id) < 1){
      return SERVER_ERROR;
    }
    
    //Pobranie aktualnych cen filmów.
    $query = "SELECT Id, Cena FROM Filmy WHERE id IN($ids) ";
    
    if(!$result = $this->dbo->query($query)){
      return SERVER_ERROR;
    }
    
    //Zapisanie danych do tabeli FilmyZamowienia.
    while($row = $result->fetch_row()){
      $id = $row[0];
      $cena = $row[1];
      $ile = $_SESSION['basket'][$row[0]];
      $query = "INSERT INTO FilmyZamowienia VALUES($id, $orderId, ";
      $query .= "$ile, $cena)";
      
      //Jeśli nie udało się wykonać zapytania.
      if(!$this->dbo->query($query)){
        return SERVER_ERROR;
      }
      
      //Jeśli liczba dodanych rekordów inna niż 1.
      if($this->dbo->affected_rows <> 1){
        return SERVER_ERROR;
      }
    }
    //Zatwierdzenie transakcji.
    $this->dbo->commit();
    
    //Wyczyszczenie koszyka.
    $_SESSION['basket'] = array();
    
    return ACTION_OK;
  }
}
?>
