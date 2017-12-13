<?php
echo '<!DOCTYPE html>';
echo '<html lang="en">';
echo '<head>';
echo '<meta charset="utf-8">';
echo '<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1">';

// Normalize first, so framework and my changes are not overridden.
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.css" ' .
  'integrity="sha256-sxzrkOPuwljiVGWbxViPJ14ZewXLJHFJDn0bv+5hsDY=" crossorigin="anonymous" />';

/*
echo '<link rel="apple-touch-icon" sizes="57x57" href="icon/apple-icon-57x57.png">';
echo '<link rel="apple-touch-icon" sizes="60x60" href="icon/apple-icon-60x60.png">';
echo '<link rel="apple-touch-icon" sizes="72x72" href="icon/apple-icon-72x72.png">';
echo '<link rel="apple-touch-icon" sizes="76x76" href="icon/apple-icon-76x76.png">';
echo '<link rel="apple-touch-icon" sizes="114x114" href="icon/apple-icon-114x114.png">';
echo '<link rel="apple-touch-icon" sizes="120x120" href="icon/apple-icon-120x120.png">';
echo '<link rel="apple-touch-icon" sizes="144x144" href="icon/apple-icon-144x144.png">';
echo '<link rel="apple-touch-icon" sizes="152x152" href="icon/apple-icon-152x152.png">';
echo '<link rel="apple-touch-icon" sizes="180x180" href="icon/apple-icon-180x180.png">';
echo '<link rel="icon" type="image/png" sizes="192x192"  href="icon/android-icon-192x192.png">';
echo '<link rel="icon" type="image/png" sizes="32x32" href="icon/favicon-32x32.png">';
echo '<link rel="icon" type="image/png" sizes="96x96" href="icon/favicon-96x96.png">';
echo '<link rel="icon" type="image/png" sizes="16x16" href="icon/favicon-16x16.png">';
echo '<link rel="manifest" href="icon/manifest.json">';
*/
echo '<meta name="msapplication-TileColor" content="#ffffff">';
echo '<meta name="msapplication-TileImage" content="icon/ms-icon-144x144.png">';
echo '<meta name="theme-color" content="#ffffff">';

// Bootstrap and jQuery. Order is important, so both here...
echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" ' .
  'integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">';
echo '<script src="https://code.jquery.com/jquery-3.2.1.min.js" ' .
  'integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>';
echo '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" ' .
  'integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>';
// Optional Bootstrap theme
echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"' .
  ' integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">';

// Font-awesome
echo '<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" ' .
  'rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">';

// modalEffects support
echo '<link type="text/css" media="all" rel="stylesheet" href="../css/modalEffect.css">';

// Site specific styles
echo '<link type="text/css" media="all" rel="stylesheet" href="../css/v4lStyle.css">';


// sorttable.js (see: https://www.kryogenix.org/code/browser/sorttable/)
echo '<script src="../js/sorttable.js"></script>';

// TinyMCE for HTML Edit Controls
echo '<script type="text/javascript" src="../js/tinymce/tinymce.min.js" ></script >';

// ModalEffects support
// Need to be at bottom of page?
// echo '<script src="js/modalEffects.js"></script>';

echo '<title>' . $string . '</title>';
echo '</head>';

?>