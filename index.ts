import { UserConfig, searchForWorkspaceRoot } from 'vite'
import path from 'path'

/**
 * Export vite plugin as default
 */
export default () => ({

  name: 'ja/paypal',

  config: (config: UserConfig): UserConfig => {

    config.resolve = config.resolve || {}

    config.resolve.alias = {
      ...(config.resolve.alias || {}),
      '@ja/paypal': path.resolve(`${__dirname}/resources/js`),
    }

    return config
  }
})
