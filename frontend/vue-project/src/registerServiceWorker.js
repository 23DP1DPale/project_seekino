export function registerServiceWorker() {
  if (!('serviceWorker' in navigator) || import.meta.env.DEV) {
    return
  }

  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch(() => {
      // PWA reģistrācijas kļūda nedrīkst apturēt galveno lietotnes darbību.
    })
  })
}
