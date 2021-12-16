<?php
// config for Trinityrank/LaravelSchemaOrgBuilder
return [
    'general' => [
        'name' => '', // This name is used in multiple places - Organization legalName/name/brand, WebSite name
        'logo' => '', // Path to logo
        'inLanguage' => [
            'en-US'
        ]
    ],
    'organization' => [
        'sameAs' => [], // Social network URLs
        'slogan' => ''
    ],
    'review' => [
        'relevant_decorators' => [''] // Review decorators which schema can search for generating additional info
    ]
];
