<?php
class Registration
{
  private $dbo = null;
  private $fields = array(
    'email' => 'Adres e-mail',
    'hasło' => 'Hasło',
    'hasło2' => 'Powtórz hasło',
    'imię' => 'Imię',
    'nazwisko' => 'Nazwisko',
    'ulica' => 'Ulica',
    'nr_domu' => 'Numer domu',
    'nr_mieszkania' => 'Numer mieszkania',
    'miejscowość' => 'Miejscowość',
    'kod' => 'Kod pocztowy',
    'kraj' => 'Kraj'
  );
  function __construct($dbo){
    $this->dbo = $dbo;
  }
  function showRegistrationForm()
  {
  ?>
    <div id="registrationFormDiv">
    <h2> Wprowadź dane rejestracyjne: </h2>
    <form name = "regForm"
          action = "index.php?action=registerUser"
          method = "post"
    >
    <table>
    <?php
      foreach($this->fields as $nazwa => $opis){
        echo "<tr>";
        echo "<td class='col1st'>$opis:</td>";
        echo "<td class='col2nd'>";
        
        $val = (isset($_POST[$nazwa])) ? $val = $_POST[$nazwa] : '';
        
        if($nazwa == 'hasło' || $nazwa == 'hasło2'){
          $type = 'password';
          $val = '';
        }
        else{
          $type = 'text';
        }
        
        echo "<input type='$type' name='$nazwa' value='$val' />";
        echo "</td></tr>";
      }
    ?>
    <tr>
    <td colspan="2" class="colmerged">
      <input type="submit" value="Rejestracja" />
    </td>
    </tr></table>
    </form>
    </div>
  <?php
  }
  function registerUser()
  {
    foreach($this->fields as $nazwa => $opis){
      if(!isset($_POST[$nazwa]) || $_POST[$nazwa] == ''){
        return FORM_DATA_MISSING;
      }
    }
    
    //Tutaj lub po przefiltrowaniu dodatkowa weryfikacja danych,
    //w tym sprawdzenie długości ciągów, znaków niedozwolonych itp.
    
    //Przefiltrowanie danych z formularza.
    $fieldsFromForm = array();
    foreach($this->fields as $nazwa => $opis){
      $fieldsFromForm[$nazwa] = filter_input(INPUT_POST, $nazwa, 
                                FILTER_SANITIZE_SPECIAL_CHARS);
    }
    
    //Sprawdzenie zgodności hasła z obu pól.
    if($fieldsFromForm['hasło'] != $fieldsFromForm['hasło2']){
      return 'PASSWORDS_NOT_MATCH';
    }
    unset($fieldsFromForm['hasło2']);
    unset($this->fields['hasło2']);
    
    //Zakodowanie hasła.
    $fieldsFromForm['hasło'] = crypt($fieldsFromForm['hasło']);
    
    //Przygotowanie ciągów nazw pól i wartości pól dla zapytania SQL.
    $fieldsNames = '`'.implode('`,`', array_keys($this->fields)).'`';
    $fieldsVals = '\''.implode('\',\'', $fieldsFromForm).'\'';
    
    //Formowanie i wykonanie zapytania.
    $query = "INSERT INTO Klienci ($fieldsNames) VALUES ($fieldsVals)";
    
    if($this->dbo->query($query)){
      return ACTION_OK;
    }
    else{echo $this->dbo->error;
      return ACTION_FAILED;
    }
  }
}
