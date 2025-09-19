# Changelog

## [2.0.0] - 2025-09-19

### Added
- WordPress admin interface for configuration
- HTTPS enforcement for webhook URLs
- Enhanced security with secret validation
- Comprehensive error logging
- Input sanitization and validation

### Changed
- Refactored from procedural to OOP structure
- Moved from hardcoded to database configuration
- Improved error handling and logging

### Security
- HTTPS-only webhook endpoints
- Minimum 12-character secrets
- SSL certificate verification
- Input sanitization

## [1.0.0] - 2025-09-10

### Added
- Basic webhook forwarding functionality
- Secret header authentication
- Initial MetForm integration