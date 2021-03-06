<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */


return [
    'iPavlov' => [
        'class' => \App\Domain\Strategy\iPavlov\Strategy::class,
        'components' => [
            \App\Domain\Strategy\iPavlov\Component\Settings::class,
            \App\Domain\Strategy\iPavlov\Component\TextNormalizer::class,
            \App\Domain\Strategy\iPavlov\Component\StopWordsRemover::class,
            \App\Domain\Strategy\iPavlov\Component\Embedder::class,
            \App\Domain\Strategy\iPavlov\Component\NetClassifier::class,
        ],
        'url' => env('AI_IPAVLOV_URI'),
        'api_key' => env('AI_IPAVLOV_API_KEY'),
    ],
];
