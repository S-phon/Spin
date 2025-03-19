

# Spin - A Spinning Wheel Plugin for PocketMine

Spin is an exciting and customizable plugin for PocketMine servers that introduces a spinning wheel feature. Players can spin the wheel to win random items, with options to configure the radius, spin speed, and prize items.

## Features
- **Customizable Wheel**: Adjust the wheel’s radius, spin speed, and prize items via the config file.
- **Random Rewards**: Players can win random items from a predefined list.
- **Permission Control**: Restrict access to commands and management features.
- **Config Reload**: Update settings on-the-fly without restarting the server.

## Installation
Follow these steps to install the Spin plugin on your PocketMine server:

1. **Download the Plugin**:
   - Grab the latest `Spin.phar` file from the [releases page](https://github.com/S-phon/Spin/releases).

2. **Upload to Server**:
   - Copy the `Spin.phar` file into the `plugins` folder of your PocketMine server.

3. **Restart the Server**:
   - Restart your server to activate the plugin. A `config.yml` file will be automatically created in the `plugins/Spin` folder.

## Configuration
The plugin’s settings are stored in the `config.yml` file, located in `plugins/Spin`. Here’s an example of the default configuration:

```yaml
radius: 5
spin-speed: 0.1
stick-nametag: "Spin the Wheel!"
steps-up: 20
steps-down: 20
items:
  diamond:
    count: 3
  emerald:
    count: 2
  gold_ingot:
    count: 5
```

### Config Options
- **radius**: The size of the spinning wheel in blocks.
- **spin-speed**: How fast the wheel spins (higher values = faster).
- **stick-nametag**: The display name shown above the spinning stick.
- **steps-up**: Number of ticks for the selected item to rise.
- **steps-down**: Number of ticks for the selected item to fall.
- **items**: List of possible rewards and their quantities. Use PocketMine item names (e.g., `diamond`, `emerald`).

You can customize the `items` section by adding or removing items as needed.

## Commands
The plugin includes the following commands:

- **/spin**  
  Starts the spinning wheel for the player.  
  *Usage*: Simply type `/spin` in-game.

- **/spin reload**  
  Reloads the plugin’s configuration without restarting the server.  
  *Usage*: Type `/spin reload` (requires `spin.manage` permission).

## Permissions
Control access to the plugin with these permissions:

- **spin.use**  
  Allows players to use the `/spin` command.  
  *Default*: `true` (available to all players).

- **spin.manage**  
  Allows players to reload the config with `/spin reload`.  
  *Default*: `op` (restricted to operators).

You can manage these permissions using a plugin like PurePerms or LuckPerms.

## Troubleshooting
Here are some common issues and solutions:

- **"Yo, wtf is this item: [item_name]"**  
  This means an item in the config isn’t recognized. Double-check the item name against PocketMine’s item list.

- **"Chill, fam! One spin at a time, wait it out!"**  
  A spin is already in progress. Wait until it finishes before starting another.

## Support
Need help? Have a question? Open an issue on the [GitHub repository](https://github.com/S-phon/Spin/issues) for assistance.

