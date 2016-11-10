var _ = require("lodash");

function quintypeLoadMore (container, params, api, storyTemplate) {
  var api_search = '/api/v1/search?';
  var moreStoriesTemplate = require("./templates")[storyTemplate];
  var loadButton = container.find('#load-more-button');
  var loadImage = container.find('#load-more-loader');
  params = _.extend({limit: 20}, params);
  container.find("#load-more-button").click(function() {
    loadImage.show();
    $.get(api, _.extend(params, {offset: params.offset}), function(response){
      if (api.indexOf(api_search) != -1){ //check for search api
        var stories = response.results.stories;
      } else {
        var stories = response.stories;
      }
      if(stories.length > 0) {
        params.offset += params.limit;
        container.find(".load-more-results").append(moreStoriesTemplate.render({stories: stories}));
      } else{
        loadButton.hide();
      }
      loadImage.hide();
    });
  });
}

module.exports = quintypeLoadMore;
