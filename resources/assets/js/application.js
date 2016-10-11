global.app = {
  initQtReady: require("./qt_ready"),
  startHomePreview: require("./home_preview"),
  video: require("./video"),
  quintypeLoadMore : require("./load_more"),
  mapOverlay : require("./map_overlay"),
  slickSlideShow : require("./slick_slideShow_settings")
};

$(document).ready(function() {
  app.slickSlideShow.slickSettings();
  app.video.setupYoutubeVideo();
  app.video.loadYoutubeLibrary();
  app.mapOverlay.locationOverlay();
  app.initQtReady();
});
