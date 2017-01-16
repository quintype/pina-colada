<?php

use App\Api\FocusedImage;

function assetPath($file)
{
    $cdn = config('quintype.asset-host');

    return $cdn.elixir($file, config('quintype.publisher-name').'/assets');
}

function focusedImageUrl($slug, $aspectRatio, $metadata, $opts)
{
    $cdn = config('quintype.image-cdn');
    $image = new FocusedImage($slug, $metadata);

    return $cdn.'/'.$image->path($aspectRatio, $opts);
}

function menuBase($menuType)
{
    if ($menuType == 'section') {
        return '/section/';
    } else {
        return '';
    }
}

function getPhotoStoryImages($story) {
  $photoArray = [
    ['image-s3-key' => $story['hero-image-s3-key'],
    'image-metadata' => $story['hero-image-metadata'],
    'title' => $story['hero-image-caption'], ],
  ];
  foreach ($story['cards'] as $card) {
    foreach ($card['story-elements'] as $key => $element) {
      if ($element['type'] == 'image') {
        array_push($photoArray, ['image-s3-key' => $element['image-s3-key'],
        'image-metadata' => $element['image-metadata'],
        'title' => $element['title'], ]);
      }
    }
   }
  return $photoArray;
}
