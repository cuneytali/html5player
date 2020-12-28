var player = document.getElementById('audio'); // id for audio element
var duration; // Duration of audio clip
btnPlayPause = document.getElementById('btnPlayPause');
btnMute = document.getElementById('btnMute');
progressBar = document.getElementById('progress-bar');
volumeBar = document.getElementById('volume-bar');
source = document.getElementById('audioSource');

// Update the video volume
volumeBar.addEventListener("change", function(evt) {
    function displayMessage(message, canPlay) {
        var element = document.querySelector('#message');
        element.innerHTML = message;
        element.className = canPlay ? 'info' : 'error';
    }

    player.volume = parseInt(evt.target.value) / 10;
});

// Add a listener for the timeupdate event so we can update the progress bar
player.addEventListener('timeupdate', updateProgressBar, false);

// Add a listener for the play and pause events so the buttons state can be updated
player.addEventListener('play', function() {
    // Change the button to be a pause button
    changeButtonType(btnPlayPause, 'pause');
}, false);

player.addEventListener('pause', function() {
    // Change the button to be a play button
    changeButtonType(btnPlayPause, 'play');
}, false);

player.addEventListener('volumechange', function(e) {
    // Update the button to be mute/unmute
    if (player.muted) changeButtonType(btnMute, 'unmute');
    else changeButtonType(btnMute, 'mute');
}, false);

player.addEventListener('ended', function() {
    this.pause();
}, false);

progressBar.addEventListener("click", seek);

function seek(e) {
    if (player.src) {
        var percent = e.offsetX / this.offsetWidth;
        player.currentTime = percent * player.duration;
        e.target.value = Math.floor(percent / 100);
        e.target.innerHTML = progressBar.value + '% played';
    }
}

function playPauseAudio() {
    if (player.src) {
        if (player.paused || player.ended) {
            // Change the button to a pause button
            changeButtonType(btnPlayPause, 'pause');
            player.play();
        } else {
            // Change the button to a play button
            changeButtonType(btnPlayPause, 'play');
            player.pause();
        }
    }
}

// Stop the current media from playing, and return it to the start position
function stopAudio() {
    if (player.src) {
        player.pause();
        if (player.currentTime) player.currentTime = 0;
    }
}

// Toggles the media player's mute and unmute status
function muteVolume() {
    if (player.src) {
        if (player.muted) {
            // Change the button to a mute button
            changeButtonType(btnMute, 'mute');
            player.muted = false;
        } else {
            // Change the button to an unmute button
            changeButtonType(btnMute, 'unmute');
            player.muted = true;
        }
    }
}

// Replays the media currently loaded in the player
function replayAudio() {
    if (player.src) {
        resetPlayer();
        player.play();
    }
}


// Update the progress bar
function updateProgressBar() {
    // Work out how much of the media has played via the duration and currentTime parameters
    var percentage = Math.floor((100 / player.duration) * player.currentTime);
    // Update the progress bar's value
    progressBar.value = percentage;
    // Update the progress bar's text (for browsers that don't support the progress element)
    progressBar.innerHTML = progressBar.title = percentage + '% played';
}

// Updates a button's title, innerHTML and CSS class
function changeButtonType(btn, value) {
    btn.title = value;
    btn.innerHTML = value;
    btn.className = value;
}

function resetPlayer() {
    progressBar.value = 0;
    //clear the current song
    player.src = '';
    // Move the media back to the start
    player.currentTime = 0;
    // Set the play/pause button to 'play'
    changeButtonType(btnPlayPause, 'play');
}

function displayMessage(message, canPlay) {
    var element = document.querySelector('#message');
    element.innerHTML = message;
    element.className = canPlay ? 'info' : 'error';
}

function playSelectedFile(event) {
    var file = this.files[0],
        type = file.type,
        canPlay = player.canPlayType(type),
        message = 'Can play type "' + type +
        '": ' + (canPlay ? canPlay : 'no');
    displayMessage(message, canPlay);

    if (canPlay) player.src = URL.createObjectURL(file);
    else resetPlayer();
}

var inputNode = document.querySelector('input');
inputNode.addEventListener('change', playSelectedFile, false);

$("#btnPlayList").click(function() {
    $("#playlist").toggle();
});