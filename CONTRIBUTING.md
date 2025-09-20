# Contributing to MetForm → n8n Webhook Bridge

Thank you for your interest in contributing to this project! We welcome contributions from the community and are excited to see what you can bring to the table.

## 🤝 Ways to Contribute

- **🐛 Report bugs** - Help us identify and fix issues
- **💡 Suggest features** - Propose new functionality or improvements
- **📝 Improve documentation** - Help make our docs clearer and more comprehensive
- **💻 Submit code** - Fix bugs, implement features, or optimize performance
- **🧪 Test the plugin** - Try it in different environments and report your experience
- **🌍 Translations** - Help make the plugin accessible in more languages

## 📋 Before You Start

### Prerequisites
- WordPress development environment
- PHP 7.4+ knowledge
- Basic understanding of MetForm and n8n
- Familiarity with WordPress plugin development

### Recommended Setup
- Local WordPress installation (XAMPP, Local, MAMP, etc.)
- MetForm plugin installed
- n8n instance for testing (local or cloud)
- Code editor with PHP support
- Git for version control

## 🐛 Reporting Bugs

Before creating a bug report, please:

1. **Check existing issues** - Your bug might already be reported
2. **Test with latest version** - Ensure you're using the most recent release
3. **Reproduce the bug** - Try to consistently reproduce the issue

### Bug Report Template

```markdown
**Bug Description**
A clear and concise description of what the bug is.

**To Reproduce**
Steps to reproduce the behavior:
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

**Expected Behavior**
A clear description of what you expected to happen.

**Screenshots**
If applicable, add screenshots to help explain your problem.

**Environment:**
- WordPress version: [e.g. 6.3]
- MetForm version: [e.g. 3.8.0]
- Plugin version: [e.g. 2.0.0]
- PHP version: [e.g. 8.1]
- Browser: [e.g. Chrome 118]

**Additional Context**
- Error logs (if any)
- n8n workflow details
- Any other relevant information
```

## 💡 Suggesting Features

We love new ideas! Before suggesting a feature:

1. **Check the roadmap** - Review [PLAN.md](PLAN.md) to see if it's already planned
2. **Search existing issues** - Someone might have already suggested it
3. **Consider the scope** - Does it fit the plugin's core mission?

### Feature Request Template

```markdown
**Is your feature request related to a problem?**
A clear description of what the problem is. Ex. I'm always frustrated when [...]

**Describe the solution you'd like**
A clear description of what you want to happen.

**Describe alternatives you've considered**
A clear description of any alternative solutions you've considered.

**Implementation Ideas**
If you have technical ideas on how this could be implemented.

**Additional context**
Add any other context, mockups, or examples about the feature request.
```

## 💻 Code Contributions

### Development Workflow

1. **Fork the repository**
2. **Create a feature branch** from `main`
   ```bash
   git checkout -b feature/your-feature-name
   ```
3. **Make your changes**
4. **Test thoroughly**
5. **Commit with clear messages**
6. **Push to your fork**
7. **Create a Pull Request**

### Coding Standards

We follow **WordPress Coding Standards**. Please ensure your code:

#### PHP Standards
- Use WordPress coding style
- Follow PSR-4 autoloading for classes
- Include proper PHPDoc comments
- Use meaningful variable and function names
- Handle errors gracefully

```php
/**
 * Example of good PHP documentation
 *
 * @param int    $form_id The MetForm form ID
 * @param array  $response The form submission data
 * @return bool  True on success, false on failure
 */
public function handle_form_submission($form_id, $response) {
    // Implementation
}
```

#### Security Standards
- **Always sanitize inputs** using WordPress functions
- **Validate data** before processing
- **Use nonces** for admin forms
- **Check capabilities** for admin actions
- **Escape outputs** when displaying data

```php
// Good examples
$webhook_url = esc_url_raw($_POST['webhook_url']);
$secret = sanitize_text_field($_POST['secret']);
wp_verify_nonce($_POST['_wpnonce'], 'metform_n8n_settings');
```

#### Database Standards
- Use WordPress database prefix
- Prepare queries with `$wpdb->prepare()`
- Create proper indexes
- Include rollback procedures

```php
// Good example
$wpdb->query($wpdb->prepare(
    "INSERT INTO {$wpdb->prefix}metform_n8n_queue (form_id, payload) VALUES (%d, %s)",
    $form_id,
    $payload
));
```

### Commit Message Guidelines

Use clear, descriptive commit messages:

```bash
# Good examples
git commit -m "Add retry logic for failed webhooks"
git commit -m "Fix SSL certificate validation issue"
git commit -m "Update admin interface styling"

# Avoid
git commit -m "Fix bug"
git commit -m "Update stuff"
git commit -m "Changes"
```

### Testing

Before submitting a PR, please:

- [ ] Test with different WordPress versions (5.0+)
- [ ] Test with different PHP versions (7.4+)
- [ ] Test with MetForm Free and Pro
- [ ] Test webhook functionality with actual n8n instance
- [ ] Check for PHP errors and warnings
- [ ] Validate HTML output
- [ ] Test admin interface in different browsers

## 📖 Documentation

Documentation improvements are always welcome! Areas that need attention:

- **README.md** - User-facing documentation
- **TECHNICAL.md** - Developer documentation
- **Code comments** - Inline documentation
- **Admin help text** - User interface guidance

### Documentation Style
- Use clear, simple language
- Include examples where helpful
- Keep technical jargon to minimum for user docs
- Be comprehensive in technical docs

## 🌍 Internationalization (i18n)

We welcome translation contributions:

1. **Text Domain**: `metform-n8n-webhook-bridge`
2. **POT File**: Will be generated from the code
3. **Languages Needed**: All languages welcome!

### Adding Translatable Strings

```php
// Use WordPress i18n functions
__('Text to translate', 'metform-n8n-webhook-bridge');
_e('Text to translate and echo', 'metform-n8n-webhook-bridge');
esc_html__('Text to translate and escape', 'metform-n8n-webhook-bridge');
```

## 🏷️ Pull Request Guidelines

### Before Submitting
- [ ] Fork the repo and create a feature branch
- [ ] Follow coding standards
- [ ] Test thoroughly
- [ ] Update documentation if needed
- [ ] Write clear commit messages

### PR Description Template

```markdown
**Description**
Brief description of what this PR does.

**Type of Change**
- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update

**Testing**
- [ ] Tested with WordPress [version]
- [ ] Tested with MetForm [version]  
- [ ] Tested with PHP [version]
- [ ] Tested webhook functionality
- [ ] No PHP errors or warnings

**Screenshots (if applicable)**
Add screenshots to help explain your changes.

**Checklist**
- [ ] My code follows the project's coding standards
- [ ] I have performed a self-review of my code
- [ ] I have commented my code, particularly in hard-to-understand areas
- [ ] I have made corresponding changes to the documentation
- [ ] My changes generate no new warnings
```

## 🚀 Development Roadmap

Check our [PLAN.md](PLAN.md) to see what we're working on and what's planned for future versions. Great areas for contribution:

### Current Priorities (v3.0)
- Retry logic implementation
- Advanced logging system
- Statistics dashboard
- Connection testing tools

### Future Features (v4.0+)
- Multiple webhook support
- Advanced field mapping
- REST API development
- Extension system

## 📞 Getting Help

Need help with your contribution?

- **GitHub Issues** - Ask questions or start discussions
- **Documentation** - Check README.md and TECHNICAL.md
- **Code Review** - Submit draft PRs for feedback

## 🎉 Recognition

All contributors will be:
- **Listed in README.md** - Contributors section
- **Mentioned in release notes** - For significant contributions
- **Credited in commit messages** - When merging PRs

## 📜 License

By contributing to this project, you agree that your contributions will be licensed under the [MIT License](LICENSE).

---

## 🙏 Thank You!

Every contribution, no matter how small, makes this project better. We appreciate your time and effort in helping improve MetForm → n8n Webhook Bridge!

**Happy coding!** 🚀