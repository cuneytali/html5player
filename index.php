<?php
session_start();
$uniqid = session_id(); //echo $uniqid;

date_default_timezone_set('Europe/Istanbul');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pdo = new PDO('sqlite:player.sqlite');

//Unique ID kayıtlı değilse kaydetme, kayıtlı ise UserID'yi bulma
$sql = "SELECT * FROM users WHERE uniqid='" . $uniqid . "'";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll();
foreach ($rows as $row) {
    $isthereauser = $row['id'];
}
if(empty($isthereauser)) {
    $sql = 'INSERT INTO users (uniqid) VALUES ("' . $uniqid . '")'; //echo $sql;
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $sql = "SELECT * FROM users WHERE uniqid='" . $uniqid . "'"; //echo $sql;
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $userid = $row['id'];
    }
}else{
    foreach ($rows as $row) {
        $userid = $row['id'];
    }
};
//echo $userid;

//Hangi Kitap?
if(!empty($_GET['bookid'])){
    $sql = "SELECT * FROM books WHERE id='" . $_GET['bookid'] . "'"; //echo $sql;
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $bookid = $row['id'];
        $bookname = $row['name'];
        $bookpath = $row['path'];
    }
} else {
    $sql = "SELECT * FROM books WHERE id=1"; //echo $sql;
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $bookid = $row['id'];
        $bookname = $row['name'];
        $bookpath = $row['path'];
    }
}

if (!empty($_GET['trackid'])) { 
    $sql = 'INSERT INTO userstracks (user, track) VALUES (' . $userid . ',' . $_GET['trackid'] . ')';
    //echo $sql;
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    echo htmlentities($_GET['trackid']);
    die();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>MP3 Player</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
    <script>
        function setCookie(cname, cvalue, exdays) {
            var d = new Date();
            d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
            var expires = "expires=" + d.toGMTString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        }
        function getCookie(cname) {
            var name = cname + "=";
            var decodedCookie = decodeURIComponent(document.cookie);
            var ca = decodedCookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        }
        function checkCookie(cname) {
            var cookie = getCookie(cname);
            if (cookie != "") {
                alert("Welcome again " + cookie);
            } else {
                cookie = prompt("Please enter your name:", "");
                if (cookie != "" && cookie != null) {
                    setCookie(cname, cookie, 30);
                }
            }
        }
        function eraseCookie(name) {
            setCookie(name, "", -1);
        }
    </script>
</head>

<body onload="setCurFile(getCookie('dosya'));setCurActive(getCookie('aktif'));setCurTime(getCookie('lokasyon'));">
<div id="player">
    <audio id="audio" preload="auto" tabindex="0">
        <source type="audio/mp3" src="book/book-14-ingiliz-casusu/01.mp3">
        Kullandığınız internet tarayıcı programı HTML5 player desteklemiyor!
    </audio>
    <div id="audio_player">
        <button id="btnPlayList" class="btnplaylist" title="playlist">Playlist</button>
        <button id='btnReplay' class='replay' title='replay' accesskey="R" onclick='replayAudio();'>Replay</button>
        <button id='btnPlayPause' class='play' title='play' accesskey="P" onclick='playPauseAudio();'>Play</button>
        <button id='btnStop' class='stop' title='stop' accesskey="X" onclick='stopAudio();'>Stop</button>
        <button id='btnMute' class='mute' title='mute' onclick='muteVolume();'>Mute</button>
        <input type="range" id="volume-bar" title="volume" min="0" max="10" step="1" value="10">
        <progress id='progress-bar' min='0' max='100' value='0'>0% played</progress>
    </div>
    <div class="tab">
        <button class="tablinks" onclick="openTab(event, 'playlistTracks')">Playlist</button>
        <button class="tablinks" onclick="openTab(event, 'selectedTracks')">Selected</button>
    </div>

    <div id="playlistTracks" class="tabcontent" style="display:block">
        <ul id="playlist">
<?php
            $sql = 'SELECT * FROM tracks WHERE book=' . $bookid . ' ORDER BY id'; //echo $sql;
            $stmt = $pdo->query($sql); $rows = $stmt->fetchAll(); $c = 1;
            foreach ($rows as $row):
                echo '
                <li><i data-id="'.$row['id'].'" data-name="'.$row['title'].'" data-bookpath="'.$bookpath.'" data-filepath="'.$row['path'].'">+</i>
                <a id="a' . $c++ . '" href="book/' . $bookpath . '/' . $row['path'] . '">' . $row['title'] . '</a></li>';
            endforeach;
?>
        </ul>
    </div>
    <div id="selectedTracks" class="tabcontent">
        <ul id="selected">
<?php
            $sql = "SELECT * FROM tracks WHERE id IN (SELECT track FROM userstracks WHERE user =
                    (SELECT id FROM users WHERE uniqid = '" . $uniqid . "'))";
            $stmt = $pdo->query($sql); $rows = $stmt->fetchAll(); $c = 1;
            foreach ($rows as $row):
                echo '<li><i>-</i><a id="a' . $c++ . '" href="book/' . $bookpath . '/' . $row['path'] . '">' . $row['title'] . '</a></li>';
            endforeach;
?>
        </ul>
    </div>
</div>

<script src="player.js"></script>
<script>
    
    $("#playlist li i").on('click', function () {
        dataid = $(this).data("id");
        databookpath = $(this).data("bookpath");
        datafilepath =  $(this).data("filepath");
        dataname = $(this).data("name");
        $('ul#selected').prepend('<li><i>-</i><a href="book/' + databookpath + '/' + datafilepath+'">' + dataname + '</li>');
        //console.log(xyz);

        var currentLocation = window.location.href + '?&trackid=' + dataid;
        
        alert(currentLocation);

        $.get(currentLocation, function(data, status){
            alert("Data: " + data + "\nStatus: " + status);
        });
    });

    var audio; var playlist; var tracks; var current;

    init();
    initSelected();


    function init() {
        current = 0;                audio = $('audio');
        playlist = $('#playlist');  tracks = playlist.find('li a');
        len = tracks.length - 1;    audio[0].volume = .10;
        audio[0].play();

        playlist.find('a').click(function (e) {
            e.preventDefault();
            link = $(this);
            current = link.parent().index();
            run(link, audio[0]);
        });

        audio[0].addEventListener('ended', function (e) {
            current++;
            if (current == len) {
                current = 0;
                link = playlist.find('a')[0];
            } else {
                link = playlist.find('a')[current];
            }
            run($(link), audio[0]);
        });
    }

    function initSelected() {
        current = 0;                audio = $('audio');
        playlist = $('#selected');  tracks = playlist.find('li a');
        len = tracks.length - 1;    audio[0].volume = .10;

        playlist.find('a').click(function (e) {
            e.preventDefault();
            link = $(this);
            current = link.parent().index();
            run(link, audio[0]);
        });

        audio[0].addEventListener('ended', function (e) {
            current++;
            if (current == len) {
                current = 0;
                link = playlist.find('a')[0];
            } else {
                link = playlist.find('a')[current];
            }
            run($(link), audio[0]);
        });
    }

    function run(link, player) {
        player.src = link.attr('href');
        par = link.parent();

        setCookie("aktif", link.attr("id"), 30);

        par.addClass('active').siblings().removeClass('active');
        audio[0].load();
        audio[0].play();
    }

    var myaudio = document.getElementById("audio");

    function getCurTime() { alert(myaudio.currentTime); }
    function setCurTime(time) { myaudio.currentTime = time; }
    function setCurFile(file) { myaudio.src = file; }
    function setCurActive(id) { var idstring = "#" + id; $(idstring).parent().addClass('active').siblings().removeClass('active'); }

    window.onbeforeunload = function () {
        eraseCookie("lokasyon");
        setCookie("lokasyon", myaudio.currentTime, 30);
        setCookie("dosya", myaudio.src, 30);
    };

    function openTab(evt, cityName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) { tabcontent[i].style.display = "none"; }
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) { tablinks[i].className = tablinks[i].className.replace(" active", ""); }
        document.getElementById(cityName).style.display = "block";
        evt.currentTarget.className += " active";
    }

</script>
</body>

</html>