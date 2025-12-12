<?php

namespace App\Services;

use App\Models\ReviewImage;
use Illuminate\Support\Facades\Http;

class GoogleReviewPhotoService
{
    public function fetchAndStorePhotos()
    {
        $apiKey  = env('GOOGLE_PLACES_API_KEY');
        $placeId = env('GOOGLE_PLACE_ID');

        $url = "https://maps.googleapis.com/maps/api/place/details/json";

        $response = Http::get($url, [
            'place_id' => $placeId,
            'fields'   => 'photos',
            'key'      => $apiKey
        ])->json();

        if (($response['status'] ?? '') !== 'OK') {
            return [
                'success' => false,
                'message' => $response['error_message'] ?? 'Google API error',
            ];
        }

        $photos = $response['result']['photos'] ?? [];

        $saved = 0;

        foreach ($photos as $index => $photo) {

            $photoRef = $photo['photo_reference'];

            $imageUrl = "https://maps.googleapis.com/maps/api/place/photo?" . http_build_query([
                'maxwidth'        => 1600,
                'photo_reference' => $photoRef,
                'key'             => $apiKey,
            ]);

            ReviewImage::updateOrCreate(
                [
                    'place_id' => $placeId,
                    'photo_index' => $index,        // <-- UNIQUE
                ],
                [
                    'google_photo_reference' => $photoRef,
                    'image_url'              => $imageUrl,
                ]
            );

            $saved++;
        }



        return [
            'success' => true,
            'saved'   => $saved,
            'total'   => count($photos),
        ];
    }
}
