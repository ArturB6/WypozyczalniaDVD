<?php
if(!isset($komunikat)){
  $komunikat = "Wprowadź e-mail i hasło:";
}
?>
<div id='loginFormMsgDiv'>
<?php echo $komunikat; ?>
</div>
<form action = "index.php?action=login"
      method = "post">
<table>
<tr>
<td>E-mail:</td>
<td>
  <input type="text" name="email">
</td>
</tr><tr>
<td>Hasło:</td>
<td>
  <input type="password" name="haslo">
</td>
</tr><tr>
<td>
  <a href="index.php?action=showRegistrationForm">Rejestracja</a>
</td>
<td style="text-align:right;">
  <input type="submit" value="Wejdź">
</td>
</tr></table>
</form>
</div>
