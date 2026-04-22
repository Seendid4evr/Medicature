# Medicature Mobile App (Capacitor)

This folder contains the configuration to package Medicature as a native Android or iOS app using **Capacitor**.

## Prerequisites
1. **Node.js** - Download from https://nodejs.org (v18+)
2. **Android Studio** - For building Android APK (download from https://developer.android.com/studio)
3. **Java JDK 17+** - Required for Android builds

## Setup Steps (One-Time)

### Step 1: Install Node dependencies
Open a terminal in this `mobile/` folder:
```bash
npm install
```

### Step 2: Add the Android platform
```bash
npx cap add android
```

### Step 3: Sync the web app into the mobile wrapper
```bash
npx cap sync android
```

### Step 4: Open in Android Studio to build & run
```bash
npx cap open android
```
Then in Android Studio, click **â–¶ Run** to launch on an emulator or connected phone.

---

## Generating a Release APK (for distribution)

In Android Studio:
1. `Build` â†’ `Generate Signed Bundle / APK`
2. Choose `APK` â†’ create a keystore â†’ build Release APK
3. Share the `.apk` file with users or upload to Google Play Store

---

## Configuration

Edit `capacitor.config.json` to change:
- `appId` â€” Your unique app bundle ID (e.g. `com.yourname.medicature`)
- `server.url` â€” Point to your live server URL once deployed (e.g. `https://medicature.yoursite.com/pages/dashboard.php`)

---

## When the App is Live on a Real Server

Change `server.url` in `capacitor.config.json` from the localhost address to your production URL:
```json
"server": {
  "url": "https://your-production-domain.com/medicature/pages/dashboard.php"
}
```
Then run `npx cap sync android` again and rebuild.

---

## Features Enabled by Capacitor
- ðŸ“² **Native Push Notifications** - Remind users to take their medicine
- ðŸ”” **Local Notifications** - Scheduled medication alarms
- ðŸŒ **Offline Support** - Via the PWA service worker (sw.js)
- ðŸ“¦ **Google Play / App Store submission** - Full native packaging
