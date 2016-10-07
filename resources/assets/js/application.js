global.app = {
  initQtReady: require("./qt_ready"),
  startHomePreview: require("./home_preview"),
  quintypeLoadMore : require("./load_more"),
  video: require("./video"),
  locationOverlay: require("./location_overlay")
};

$(document).ready(function() {
  app.video.setupYoutubeVideo();
  app.video.loadYoutubeLibrary();
  app.locationOverlay.locationOverlayForMaps();
});
