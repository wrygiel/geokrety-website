<?php

function getTheme() {
    $themeTitle = _('Theme');
    $geokretyIcons = _('Icons used');
    $geokretyType = _('type');
    $themeHtml = <<<EOTHEME

<a id="websitetheme"></a>
<div class="rozdzial">$themeTitle</div>

<a id="Chooselogtype"></a>
<div class="rozdzial">$geokretyIcons</div>

EOTHEME;
    $themeHtml .= <<<EOTHEME
<table class="logtypes" style="padding:15px;">
  <thead>
    <tr>
      <th colspan="2">$geokretyType</th>
      <th>Drop</th>
      <th>Grab</th>
      <th>Comment</th>
      <th>Met</th>
      <th>Archive</th>
      <th>Dip</th>
      <th>Unknown</th>
      <th>Grab or dip by the owner</th>
    </tr>
  </thead>
EOTHEME;
    $themeHtml .= themeIconRow('0', _('Traditional'));
    $themeHtml .= themeIconRow('1', _('Book'));
    $themeHtml .= themeIconRow('2', _('Human'));
    $themeHtml .= themeIconRow('3', _('Coin'));
    $themeHtml .= themeIconRow('4', _('Stamp'));
    $themeHtml .= <<<EOTHEME
</table>
EOTHEME;
    return $themeHtml;
}

function themeIconRow($iconDigit, $iconDescription) {
    $iconRow .= <<<EOROW
  <tr>
    <td class="mid"><img src="https://cdn.geokrety.org/images/log-icons/$iconDigit/icon.jpg" alt="$iconDescription" /> </td>
    <td>$iconDescription</td>
    <td><img src="https://cdn.geokrety.org/images/log-icons/$iconDigit/10.png"/></td>
    <td><img src="https://cdn.geokrety.org/images/log-icons/$iconDigit/11.png"/></td>
    <td><img src="https://cdn.geokrety.org/images/log-icons/$iconDigit/12.png"/></td>
    <td><img src="https://cdn.geokrety.org/images/log-icons/$iconDigit/13.png"/></td>
    <td><img src="https://cdn.geokrety.org/images/log-icons/$iconDigit/14.png"/></td>
    <td><img src="https://cdn.geokrety.org/images/log-icons/$iconDigit/15.png"/></td>
    <td><img src="https://cdn.geokrety.org/images/log-icons/$iconDigit/19.png"/></td>
    <td><img src="https://cdn.geokrety.org/images/log-icons/$iconDigit/18.png"/></td>
  </tr>
EOROW;
    return $iconRow;
}