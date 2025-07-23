jQuery(document).ready(function ($) {
    const watchVideoBtn = $('#wpea-watch-video-btn');
    const videoPopup    = $('#wpea-wizard-video-popup');
    const videoFrame    = $('#wpea-wizard-video-frame');
    const closePopup    = $('#wpea-wizard-close-popup');

    // YouTube Video URL - replace with your own
    const videoURL = "https://www.youtube.com/embed/swl_2OqXTnc?si=A_jRhlcNqYWIQETt&autoplay=1";

    // Open the popup and set video source
    watchVideoBtn.on('click', function () {
        videoFrame.attr('src', videoURL);
        videoPopup.css('display', 'flex');
    });

    // Close popup on close button click
    closePopup.on('click', function () {
        videoFrame.attr('src', '');
        videoPopup.css('display', 'none');
    });

    // Close popup when clicking outside the video frame
    videoPopup.on('click', function (e) {
        if (e.target === this) {
            videoFrame.attr('src', '');
            videoPopup.css('display', 'none');
        }
    });
});
