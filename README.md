# DaEconomy

DaEconomy is a modern, lightweight, and highly efficient economy system designed specifically for PocketMine-MP 5 servers. It provides a clean base for managing your server's player balances with customizable configuration options.

## Features

* **Lightweight Storage**: Saves player data smoothly inside a dedicated `players.yml` file.
* **Highly Customizable**: Easily modify your starting money values and choose your own currency symbol (e.g., $, €, £).
* **Safe Input Handling**: Robust validation system that prevents players and administrators from inputting invalid characters or negative values, keeping your economy safe.
* **Full Permission Support**: Works seamlessly with default PocketMine-MP permissions or any permission manager plugin.

## Commands

| Command | Description | Usage | Permission | Default |
|---------|-------------|-------|------------|---------|
| `/money` | Check your current balance | `/money` | `daeconomy.command.money` | Everyone |
| `/addmoney` | Add a specific amount of money to a player | `/addmoney <player> <amount>` | `daeconomy.command.addmoney` | OP Only |
| `/removemoney` | Take away a specific amount of money from a player | `/removemoney <player> <amount>` | `daeconomy.command.removemoney` | OP Only |

## Configuration

When the plugin enables for the first time, a `config.yml` file will be generated in your `plugin_data/DaEconomy/` directory. You can customize the following options:

```yaml
# The amount of money a new player receives when they join the server for the first time
starting-money: 1000

# The prefix symbol used alongside currency amounts in messages
currency-symbol: "$"
