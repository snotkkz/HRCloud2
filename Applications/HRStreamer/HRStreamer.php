<html>
  <head>
    <title>HRStreamer</title>
  </head>
  <script type="text/javascript" src="Applications/jquery-3.1.0.min.js"></script>
  <script type="text/javascript" src="Applications/HRStreamer/HRStreamerLib.js"></script>  
  <link rel="stylesheet" href="Applications/displaydirectorycontents_72716/style.css">
  <body style="font-family:<?php echo $Font; ?>;">
<?php 
// / Set global variables.
$hrstreamerAppVersion = 'v1.0';

// / Load the HRCloud2 commonCore.
$CCFile = 'commonCore.php';
if (!file_exists($CCFile)) {
  echo nl2br('ERROR!!! HRS26, CommonCore was not detected on the server.'."\n"); }
  else {
    require_once($CCFile); } 

// / Load the getid3 library.
$getID3File = 'Applications/getid3/getid3/getid3.php';
if (!file_exists($getID3File)) {
  $txt = ('ERROR!!! HRS53, The getID3 module is not installed on the server on '.$Time.'!');
  $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL , FILE_APPEND);
  die('ERROR!!! HRS53, The getID3 module is not installed on the server on '.$Time.'!'); }
  else {
    require_once($getID3File); } 

// / Set POST variables.-
if(isset($_GET['playlistSelected']) or isset($_POST['playlistSelected'])) {
  $_POST['playlistSelected'] = $_GET['playlistSelected']; }

// / The following code is performed whnenever there is a playlistSelected.
if(isset($_GET['playlistSelected']) or isset($_POST['playlistSelected'])) {
  $PlaylistName = $_POST['playlistSelected'];  
  $PlaylistDir = $CloudTempDir.'/'.$PlaylistName;
  $PlaylistCloudDir = $CloudDir.'/'.$PlaylistName;
  if (!file_exists($PlaylistDir)) {
    mkdir($PlaylistDir, $ILPerms);
    copy($InstLoc.'/index.html', $PlaylistDir.'/indes.html');
    foreach ($iterator = new \RecursiveIteratorIterator (
      new \RecursiveDirectoryIterator ($PlaylistCloudDir, \RecursiveDirectoryIterator::SKIP_DOTS),
      \RecursiveIteratorIterator::SELF_FIRST) as $item) {
      $PD = $PlaylistDir.DIRECTORY_SEPARATOR.$iterator->getSubPathName();
        if (is_dir($item)) {
          if (!is_dir($PD)) {
            mkdir($PD, $CLPerms); 
            continue; } }
        else {
            if (!is_link($item) && !file_exists($PD)) {
              symlink($item, $PD); } } } }
    if (!file_exists($PlaylistDir)) {
      $txt = ('ERROR!!! HRS86, The PlaylistDir does not exist on '.$Time.'!');
      $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL , FILE_APPEND);
      die($txt); }
  $PlaylistNameRAW = str_replace('.Playlist', '', $PlaylistName); }
  else {
    $txt = ('ERROR!!! HRS70, There was no playlist selected on '.$Time.'!');
    $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL , FILE_APPEND);
    die($txt); }
?>
  <div align="center">
  <h3><?php echo $PlaylistNameRAW; ?></h3>
  </div>
  <hr />
<?php  
  // / If the selected playlist name does not contain .playlist, kill the script.
  if (strpos($PlaylistDir, '.Playlist') == FALSE) {
    $txt = ('ERROR!!! HRS60, The selected playlist is not a valid HRCloud2 ".playlist" file on '.$Time.'!');
    $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL , FILE_APPEND);
    die('ERROR!!! HRS60, The selected playlist is not a valid HRCloud2 ".playlist" file on '.$Time.'!'); }
// If the playlist file exists, read the album art and song data to separate arrays.
if (file_exists($PlaylistDir)) {
  $txt = ('OP-Act: User '.$UserID.' initiated HRStreamer on '.$Time.'.');
  $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL , FILE_APPEND);
  $PLImageArr = array('jpg', 'jpeg', 'bmp', 'png', 'gif');  
  $PLAudioArr =  array('mp3', 'mp4', 'wma', 'wav', 'aac');
  $PLAudioOGGArr =  array('ogg');  
  $PlaylistArtArr = array();
  $PlaylistSongArr = array();
  $PlaylistCacheDir = $PlaylistDir.'/.Cache';
  $PlaylistCacheFile = $PlaylistCacheDir.'/cache.php';
  $PlaylistFiles = scandir($PlaylistDir); 
  if (!file_exists($PlaylistCacheFile)) {
    $txt = ('ERRPR!!! HRS79, This Playlist does not contain a valid cache file on '.$Time.'.');
    $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL , FILE_APPEND); 
    die($txt); }
  if (strpos($PlaylistDir, '.Playlist') == FALSE) {
    $txt = ('ERROR!!! HRS68, '.$PlaylistDir.' is not a valid .Playlist file!');
    $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL , FILE_APPEND);
    die($txt); }
  require($PlaylistCacheFile);
  // / Separate the album art from the songs within the $PlaylistFiles.
    $PLCount = 0;
    $PLImageCount = 0;
    $PLSongCount = 0;
  foreach ($PlaylistFiles as $PlaylistFile) {
    if ($PlaylistFile == '.' or $PlaylistFile == '..' or $PlaylistFile == '.Cache' or is_dir($PlaylistFile)) continue;     
      $PLCount++; 
      $pathname = $PlaylistDir.'/'.$PlaylistFile;
      $newPathname = $PlaylistDir.'/'.$PlaylistName.'.ogg';
      $filename = pathinfo($pathname, PATHINFO_FILENAME);
      $oldExtension = pathinfo($pathname, PATHINFO_EXTENSION);
    if (in_array($oldExtension, $PLAudioOGGArr)) {
      array_push($PlaylistSongArr, $PlaylistFile); 
      $PLSongCount++;}
    if (in_array($oldExtension, $PLImageArr)) {
      array_push($PlaylistArtArr, $PlaylistFile); 
      $PLImageCount++; } } }
usleep(300);
if (!file_exists($PlaylistDir)) { 
  $txt = ('ERROR!!! HRS122, The selected playlist does not exist on '.$Time.'!');
  $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL , FILE_APPEND);
  die('ERROR!!! HRS122, The selected playlist does not exist on '.$Time.'!'); }
?>      
<div>
<?php
$SongCount = 0;
echo ('<div align="center" style="padding-bottom:2px; width:200px; float:right; clear:right;"><strong>Music</strong>');
foreach ($PlaylistSongArr as $PlaylistSong) {
  if ($PlaylistSong == '.' or $PlaylistSong == '..' or is_dir($PlaylistSong)) continue;
    $SongCount++; 
    echo('</div><div id="PlaylistSong'.$SongCount.'" name="PlaylistSong'.$SongCount.'" style="display:block; width:200px; float:right; clear:right;"><hr />'); ?>
    <div align="left"><p><strong><i><a style="float:left;"><?php echo $SongCount.'. '; ?></a></i></strong><img id="hideplay<?php echo $SongCount; ?>" name="hideplay<?php echo $SongCount; ?>" 
      onclick="stopAllAudio(); hide_visibility('hideplay<?php echo $SongCount; ?>'); show_visibility('play<?php echo $SongCount; ?>');  
        toggle_visibility('buttonbar<?php echo $SongCount; ?>');" 
      style="float:left; padding-right:5px; padding-left:5px; display:none;" src="Applications/HRStreamer/Resources/streamflipped.png">
    <img id="play<?php echo $SongCount; ?>" name="play<?php echo $SongCount; ?>" 
      onclick="stopAllAudio(); startStopSelectedAudio('song<?php echo $SongCount; ?>'); toggle_visibility('hideplay<?php echo $SongCount; ?>'); toggle_visibility('play<?php echo $SongCount; ?>'); 
        toggle_visibility('buttonbar<?php echo $SongCount; ?>');" 
      style="float:left; padding-right:5px; padding-left:5px; display:block;" src="Applications/HRStreamer/Resources/stream.png"></p></div>
  <?php
    echo nl2br("\n".'<strong>'.$PlaylistSong.'</strong>'."\n"); 
    echo nl2br('<div align="center"><p id="moreInfoLink'.$SongCount.'" style="display:block;" onclick="toggle_visibility(\'PlaylistSongInfo'.$SongCount.'\'); toggle_visibility(\'moreInfoLink'.$SongCount.'\');"><i>More Info</i></p></div>'); ?>
    <div id="PlaylistSongInfo<?php echo $SongCount; ?>" name="PlaylistSongInfo<?php echo $SongCount; ?>" style="display:none;"><?php 
    echo nl2br('<div align="center"><p onclick="toggle_visibility(\'PlaylistSongInfo'.$SongCount.'\'); toggle_visibility(\'moreInfoLink'.$SongCount.'\');"><i>Less Info</i></p></div>');
    echo nl2br('<a id="moreInfo" name="moreInfo"><i>Artist: </i>'.${'PLSongArtist'.$SongCount}."\n".'<i>Title: </i>'.${'PLSongTitle'.$SongCount}."\n".'<i>Album: </i>'.${'PLSongAlbum'.$SongCount}.'</a>'); ?>

<div align="center"><img id="FileImage" src="<?php echo ${'PLSongImage'.$SongCount};?>" style="max-width:100px; max-height:100px;" onclick="document.getElementById('AlbumImage').src='<?php echo ${'PLSongImage'.$SongCount};?>'"></div>
</div></div>
<?php }
$RandomImageFile = 'Applications/HRStreamer/Resources/RandomImageFile.png'; ?>

<div id="artwork" name="artwork" align="center" style="max-width:65%;">
  <img id="AlbumImage" name="AlbumImage" style="max-width:400px; padding-left:15px; padding-top:15px;" src="<?php echo $RandomImageFile; ?>">
</div> 

<div id="media" name="media" align="center" style="max-width:65%;">
<?php  
$SongCount = 0;    
foreach ($PlaylistFiles as $PlaylistFile) {
  if ($PlaylistFile == '.' or $PlaylistFile == '..' or $PlaylistFile == '.Cache' or is_dir($PlaylistFile)) continue;  
  $SongCount++; 
  $pathname = $PlaylistDir.'/'.$PlaylistFile; ?>
<div align="center" class='buttonbar' id='buttonbar<?php echo $SongCount; ?>' name='buttonbar' style="display:none;">
<strong><?php echo $PlaylistFile; ?></strong>
<hr />
      <div align="center" id='autosong' name='autosong'>
        <audio id="song<?php echo $SongCount; ?>" name='song<?php echo $SongCount; ?>' preload="auto" onended="toggle_visibility('play<?php echo ($SongCount + 1); ?>'); toggle_visibility('hideplay<?php echo ($SongCount + 1); ?>'); toggle_visibility('play<?php echo $SongCount; ?>'); toggle_visibility('hideplay<?php echo $SongCount; ?>'); show_visibility('buttonbar<?php echo ($SongCount + 1); ?>'); hide_visibility('buttonbar<?php echo $SongCount; ?>'); document.getElementById('song<?php echo ($SongCount + 1); ?>').play();" controls="true" src="<?php echo 'DATA/'.$UserID.'/'.$PlaylistName.'/'.$PlaylistFile; ?>" type="audio/ogg" style="width:390px;"></audio>
        <hr />
      </div> 
    </div>        
    <?php } ?> 
    </div>
  </body>
</html>