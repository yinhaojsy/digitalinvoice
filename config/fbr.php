<?php

/**
 * FBR Digital Invoicing API — sandbox only for now.
 * Production endpoints are intentionally omitted until go-live.
 *
 * @see https://download1.fbr.gov.pk/Docs/20257301172130815TechnicalDocumentationforDIAPIV1.12.pdf
 */
return [
    'environment' => 'sandbox',

    'sandbox' => [
        'validate_url' => 'https://gw.fbr.gov.pk/di_data/v1/di/validateinvoicedata_sb',
        'post_url' => 'https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata_sb',
        'base_pdi' => 'https://gw.fbr.gov.pk/pdi/v1',
        'base_pdi_v2' => 'https://gw.fbr.gov.pk/pdi/v2',
        'base_dist' => 'https://gw.fbr.gov.pk/dist/v1',
    ],

    'timeout' => (int) env('FBR_HTTP_TIMEOUT', 60),

    'scenarios' => [
        'SN001' => 'Goods at standard rate to registered buyers',
        'SN002' => 'Goods at standard rate to unregistered buyers',
        'SN003' => 'Sale of Steel (Melted and Re-Rolled)',
        'SN004' => 'Sale by Ship Breakers',
        'SN005' => 'Reduced rate sale',
        'SN006' => 'Exempt goods sale',
        'SN007' => 'Zero rated sale',
        'SN008' => 'Sale of 3rd schedule goods',
        'SN009' => 'Cotton Spinners purchase from Cotton Ginners',
        'SN010' => 'Telecom services',
        'SN011' => 'Toll Manufacturing sale by Steel sector',
        'SN012' => 'Sale of Petroleum products',
        'SN013' => 'Electricity Supply to Retailers',
        'SN014' => 'Sale of Gas to CNG stations',
        'SN015' => 'Sale of mobile phones',
        'SN016' => 'Processing / Conversion of Goods',
        'SN017' => 'Goods where FED is charged in ST mode',
        'SN018' => 'Services where FED is charged in ST mode',
        'SN019' => 'Services rendered or provided',
        'SN020' => 'Sale of Electric Vehicles',
        'SN021' => 'Sale of Cement / Concrete Block',
        'SN022' => 'Sale of Potassium Chlorate',
        'SN023' => 'Sale of CNG',
        'SN024' => 'Goods as per SRO 297(1)/2023',
        'SN025' => 'Drugs at fixed ST rate (Eighth Schedule)',
        'SN026' => 'Sale to End Consumer by retailers (standard)',
        'SN027' => 'Sale to End Consumer by retailers (3rd schedule)',
        'SN028' => 'Sale to End Consumer by retailers (reduced)',
    ],
];
