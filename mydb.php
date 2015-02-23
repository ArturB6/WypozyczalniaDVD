<?php
class MyDB extends mysqli
{
  function getQuerySingleResult($query)
  {    
    //Wykonanie zapytania.
    if(!$result = $this->query($query)){
      //echo 'Wystąpił błąd (getQuerySingleResult): nieprawidłowe zapytanie...';
      return false;
    }
    if($row = $result->fetch_row()){
      //Zwrócenie wyniku.
      return $row[0];
    }
    else{
      //Brak wyników zapytania.
      return false;
    }
  }
  function getQueryResultAsTableRows($query, $colNames = false)
  {
    //Odrzucone zapytanie
    if(!$result = $this->query($query)) return false;
    if(!$columns = $result->fetch_fields()) return false;
    
    //Zmienna przechowująca wynik działania metody.
    $str = '';
    
    //Uzyskanie nazw kolumn, o ile potrzebne.
    if($colNames){
      $str .= '<tr>';
      foreach($columns as $col){
        $str .= '<td>' . $col->name . '</td>';
      }
      $str .= '</tr>';
    }
    
    //Uzyskanie wyników zapytania.
    while($row = $result->fetch_row()){
      $str .= "<tr>";
      foreach($row as $val){
        $str .= "<td>$val</td>";
      }
      $str .= "</tr>";
    }
    
    //Zwrócenie ostatecznego wyniku.
    return $str;
  }
  function getPagination($page, $pages, $link, $msg)
  {
    $str = '';
    for($i = 0; $i < $pages; $i++){
      if($i != $page){
        $str .= "<a href=\"$link&amp;page=$i\">".($i+1)."</a>";
      }
      else{
        $str .= '<span class="activePaginationPage">' . ($i+1)
              . '</span>';
      }
      $str .= '<span class="space"> </span>';
    }
    $str = $msg . $str;
    return $str;
  }
}
?>
