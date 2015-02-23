<div id="registrationFormDiv">
<h2> Wprowadź dane rejestracyjne: </h2>
<?php if(isset($komunikat)): ?>
<div id="komunikatDiv"><?php echo $komunikat; ?></div>
<?php endif; ?>
<form name = "regForm"
      action = "index.php?action=registerUser"
      method = "post"
>
<table><tr>

<td class='col1st'>Nazwa użytkownika:</td>
<td class='col2nd'>
  <input type="text" name="nazwa" />
</td>
</tr><tr>

<td class='col1st'>Hasło:</td>
<td class='col2nd'>
  <input type="password" name="haslo" />
</td>
</tr><tr>

<td class='col1st'>Powtórz hasło:</td>
<td class='col2nd'>
  <input type="password" name="haslo2" />
</td>
</tr><tr>

<td class='col1st'>Imię:</td>
<td class='col2nd'>
  <input type="text" name="imie" />
</td>
</tr><tr>

<td class='col1st'>Nazwisko:</td>
<td class='col2nd'>
  <input type="text" name="nazwisko" />
</td>
</tr><tr>

<td class='col1st'>E-mail:</td>
<td class='col2nd'>
  <input type="text" name="email" />
</td>
</tr><tr>

<td class='col1st'>Ulica:</td>
<td class='col2nd'>
  <input type="text" name="ulica" />
</td>
</tr><tr>

<td class='col1st'>Numer domu, lok:</td>
<td class='col2nd'>
  <input type="text" name="nr" />
</td>
</tr><tr>

<td class='col1st'>Miejscowość:</td>
<td class='col2nd'>
  <input type="text" name="miejscowosc" />
</td>
</tr><tr>

<td colspan="2" class="colmerged">
  <input type="button" value="Rejestracja" />
</td>
</tr></table>
</form>
</div>
