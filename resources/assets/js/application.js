global.app = {
  initQtReady: require("./qt_ready"),
  startHomePreview: require("./home_preview"),
  video: require("./video"),
  quintypeLoadMore : require("./load_more")
};

$(document).ready(function() {
  app.initQtReady();
  app.video.setupYoutubeVideo();
  app.video.loadYoutubeLibrary();
  locationOverlay();
});

// need to fix. export to a another file

function locationOverlay() {
  $(document).on('click', '.story-element-location', function() {
    $(this).find('iframe').css('pointer-events', 'auto');
  });

  $(document).on('mouseenter', '.story-element-location', function() {
    $(this).find('iframe').css('pointer-events', 'none');
  });

  $(document).on('mouseleave', '.story-element-location', function() {
    $(this).find('iframe').css('pointer-events', 'none');
  });
}