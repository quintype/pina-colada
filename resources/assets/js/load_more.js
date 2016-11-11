var _ = require("lodash");

function quintypeLoadMore (container, params, api, storyTemplate) {
  var api_search = '/api/v1/search?';
  var moreStoriesTemplate = require("./templates")[storyTemplate];
  var loadButton = container.find('#load-more-button');
  var loadImage = container.find('#load-more-loader');
  params = _.extend({limit: 20}, params);
  container.find("#load-more-button").click(function() {
    loadButton.hide();
    loadImage.show();
    //Each time, fetch 1 story extra than the required limit so that we can use this to identify
    //whether or not to show the load more button. Once this is done, restore the original value.
    //Also, remove(slice) the extra story from being sent over to the template.
    $.get(api, _.extend(params, { offset: params.offset, limit: params.limit + 1 }), function(response){
      params.limit--;//Restoring the original value.
      if (api.indexOf(api_search) != -1){ //check for search api.
        var stories = response.results.stories;
      } else {
        var stories = response.stories;
      }
      if(stories.length > 0) {
        params.offset += params.limit;
        container.find(".load-more-results").append(moreStoriesTemplate.render({stories: _.slice(stories, 0, params.limit)}));//Slice the extra story.
        if(stories.length > params.limit){
          loadButton.show();
        }
      } else{
        loadButton.hide();
      }
      loadImage.hide();
    });
  });
}

module.exports = quintypeLoadMore;
