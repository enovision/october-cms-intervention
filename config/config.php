<?php

return [
    'thumbnailer' => [
        'thumbnailPath'                => 'thumbnailer',
        'cacheTimeout'                 => 1800,
        'cacheLocation'                => Config::get('cms.storage.uploads.folder') . '/thumbnailer',
        '404imageLocation'             => dirname(__DIR__, 1) . '/assets/images/placeholder.jpg',
        'supportedThumbnailDimensions' => [
            // width x height
            '100x100',
            '300x300',
            '300x200',
            '100x200',
            '500x500',
            '640x480',
            '1024x1024'
        ],
        'supportedQualityTags'         => [
            'poor'   => 25,
            'good'   => 50,
            'better' => 75,
            'best'   => 100,
        ],
        'supportedActions'             => [
            'fit'     => [
                'options' => [
                    'position'         => 'center',
                    'resizeCanvas'     => true, // http://image.intervention.io/api/resizeCanvas
                    'resizeRelative'   => false,
                    'canvasBackground' => 'ccc', // http://image.intervention.io/getting_started/formats
                ]
            ],
            'contain' => [
                'options' => [
                    'resizeCanvas'     => false, // http://image.intervention.io/api/resizeCanvas
                    'position'         => 'center',
                    'resizeRelative'   => true,
                    'canvasBackground' => 'ccc', // http://image.intervention.io/getting_started/formats
                ]
            ],
            'crop'    => [
                'options' => [
                    'position' => 'center', // http://image.intervention.io/api/fit
                ]
            ],
        ],
        'prefixes'                     => [
            'resize'   => [
                'default'   => 'resized',
                'greyscale' => 'resized-gs'
            ],
            'crop'     => 'cropped',
            'rotate'   => 'rotated',
            'flip'     => [
                'default' => 'flipped',
                'action'  => [
                    'v'  => 'vert',
                    'h'  => 'horz',
                    'vh' => 'verthorz'
                ]
            ],
            'colorize' => 'color',
        ]
    ]
];