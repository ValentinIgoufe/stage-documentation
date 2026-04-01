module.exports = {
  platform: 'gitlab',
  // endpoint à changer en fonction du cloud ou docker
  endpoint: 'http://gitlab/api/v4/',
  binarySource: 'install',
  token: process.env.RENOVATE_TOKEN,
  autodiscover: true,
  autodiscoverFilter: 'mon-groupe/*',
  npmrc: "legacy-peer-deps=true",
  onboarding: false, 
  onboardingConfig: {
    "extends": ["local>mon-groupe/renovate-bot:default"]
  }
};