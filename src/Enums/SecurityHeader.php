<?php

namespace AungKhant\HttpSecurityHeaders\Enums;

enum SecurityHeader: string
{
    case ContentSecurityPolicy = 'Content-Security-Policy';
    case ContentSecurityPolicyReportOnly = 'Content-Security-Policy-Report-Only';
    case CrossOriginEmbedderPolicy = 'Cross-Origin-Embedder-Policy';
    case CrossOriginEmbedderPolicyReportOnly = 'Cross-Origin-Embedder-Policy-Report-Only';
    case CrossOriginOpenerPolicy = 'Cross-Origin-Opener-Policy';
    case CrossOriginOpenerPolicyReportOnly = 'Cross-Origin-Opener-Policy-Report-Only';
    case CrossOriginResourcePolicy = 'Cross-Origin-Resource-Policy';
    case PermissionsPolicy = 'Permissions-Policy';
    case ReferrerPolicy = 'Referrer-Policy';
    case StrictTransportSecurity = 'Strict-Transport-Security';
    case XContentTypeOptions = 'X-Content-Type-Options';
    case XFrameOptions = 'X-Frame-Options';
}
