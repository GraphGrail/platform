<?php
/**
 * @author Afanasyev Pavel <p.afanasev@graphgrail.com>
 */

return [
    'iPavlov' => [
        'deeppavlov_root' => '',
        'model_path' => '',
        'dataset_reader' => [
            'name' => 'basic_classification_reader',
            'data_path' => 'data/',
            'class_sep' => '________',
        ],
        'dataset_iterator' => [
            'name' => 'basic_classification_iterator',
            'seed' => 42,
            'fields_to_merge' => [
                'train',
                'valid',
            ],
            'merged_field' => 'train',
            'field_to_split' => 'train',
            'split_fields' => [
                'train',
                'valid',
                'test',
            ],
            'split_proportions' => [
                0.8,
                0.1,
                0.1,
            ],
        ],
        'chainer' => [
            'pipe' => [
                [
                    'name' => 'stop_words_remover',
                    'id' => 'stop_words_remover',
                    'in' => ['x'],
                    'out' => ['x'],
                ],
                [
                    'name' => 'text_normalizer',
                    'id' => 'text_normalizer',
                    'in' => ['x'],
                    'out' => ['x'],
                    'norm_method' => 'lemmatize',
                    'tokenizer' => 'treebank',
                ],
                [
                    'name' => 'embedder',
                    'in' => ['x'],
                    'out' => ['xv'],
                    'load_path' => [
                        'ft_compressed.pkl',
                        'ft_compressed_local.pkl',
                    ],
                    'emb_dim' => 300,
                    'emb_len' => 50,
                    'emb_type' => 'pretrained_compressed',
                ],
                [
                    'name' => 'cnn_model',
                    'in' => ['xv'],
                    'in_y' => ['y'],
                    'out' => ['y_pred'],
                    'architecture_name' => 'dual_bilstm_cnn_model',
                    'loss' => 'categorical_crossentropy',
                    'metrics' => ['categorical_accuracy'],
                    'optimizer' => 'adam',
                    'architecture_params' => [
                        'emb_dim' => 25,
                        'dropout_power' => 0.5,
                        'seq_len' => 300,
                        'pool_size' => 2,
                        'new2old' => 'new2old.pkl',
                    ],
                    'classes' => 'class_names.pkl',
                    'confident_threshold' => 0.5,
                    'save_path' => 'cnn_weights.hdf',
                    'load_path' => 'cnn_weights.hdf5',
                ],
            ],
            'out' => [
                'y_pred',
            ],
            'in' => [
                'x',
            ],
            'in_y' => [
                'y',
            ],
        ],
        'train' => [
            'epochs' => 25,
            'validation_patience' => 10000,
            'batch_size' => 32,
            'metrics' => [
                'classification_f1',
            ],
            'val_every_n_epochs' => 25,
            'log_every_n_epochs' => 1,
            'tensorboard_log_dir' => 'logs/',
        ],
    ],
];