# Changelog

All notable changes to this project will be documented in this file.

## [1.4] - 2026-06-27
### Added
- Added a "Pronunciation Practice" feature utilizing Google Speech-to-Text via MIT App Inventor's `SpeechRecognizer` component.
- Implemented a Levenshtein distance algorithm on the frontend to calculate pronunciation accuracy by comparing spoken text to target vocabulary phrases.
- Added a dynamic language code pass-through from the web app to MIT App Inventor (`START_SPEECH_RECOGNITION:lang_code`) to ensure the native speech recognizer listens for the correct language.

### Changed
- Updated application version to `v1.4` in `index.html`.

## [1.3] - 2026-06-19
### Added
- Created `setup_db.php` script to easily automate database creation, schema setup, and clean seeding. (Removed after execution for production security).

### Changed
- **Russian Language Unicode Fix**: Restored correct UTF-8 encoding for Russian phrases inside the SQL dump (`phrases_backup.sql`), resolving the box-drawing character corruption (mojibake).
- Modified `config.php` database connection default host from `localhost` to `127.0.0.1` to prevent TCP connection refusal errors on Windows environments running XAMPP/PHP.
- Updated application version to `v1.3` in `index.html` (Welcome screen and Dashboard screen footers).

### Removed
- Cleaned up and deleted `setup_db.php` and `phrases_backup.sql` from the repository tracking to protect database queries and setup logic from public exposure.

## [1.2] - 2026-06-16
### Added
- Added app version `v1.2` to the bottom of the Welcome Screen and Dashboard Screen.
- Added MIT App Inventor WebView string signals (`PLAY_SOUND:CORRECT` and `PLAY_SOUND:WRONG`) to trigger audio cues on native mobile builds.

### Changed
- Fixed MIT App Inventor authentication session drops by adding an `Authorization` header fallback (Bearer Token login) on `auth.php`.
- Pinned Babel and React CDN versions in `index.html` to resolve syntax errors on certain browsers.

### Removed
- Deleted legacy `check_scripts.php`.

## [1.1] - 2026-06-15
### Added
- Implemented a functional Vocabulary Screen for topics: Greetings, Food, and Travel.
- Added text-to-speech (TTS) features for phrases inside lessons using high-quality/natural browser voice synthesis.
- Implemented an account deletion feature in the user dashboard with a 5-second safety confirmation window.
- Implemented an unlockable language progression system (languages cost 60 coins to unlock).

### Changed
- Swapped country/state flag images from SVG icons to real-world flag graphics (e.g. Russian flag, Malaysian state flags).
- Increased coin rewards for lesson completions to 30 coins.
- Fixed race condition/timezone issues with daily reward claims.
