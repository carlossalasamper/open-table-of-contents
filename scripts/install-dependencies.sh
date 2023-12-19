#!/bin/bash

# Function to check if a command is available
command_exists() {
  command -v "$1" >/dev/null 2>&1
}

# Function to detect the operating system
detect_os() {
  case "$OSTYPE" in
    darwin*)  echo "macos" ;;
    linux*)   echo "linux" ;;
    msys*)    echo "windows" ;;
    *)        echo "unknown" ;;
  esac
}

# Install dependencies based on the operating system
install_dependencies() {
  local os=$(detect_os)

  case "$os" in
    "macos")
      install_with_brew
      ;;
    "linux")
      install_with_apt_or_yum
      ;;
    "windows")
      install_with_choco
      ;;
    *)
      echo "Unsupported operating system."
      exit 1
      ;;
  esac
}

# Install jq with Homebrew on macOS
install_with_brew() {
  if command_exists brew; then
    brew install jq
  else
    echo "Error: Homebrew is not installed. Please install Homebrew and try again."
    exit 1
  fi
}

# Install jq with apt or yum on Linux
install_with_apt_or_yum() {
  if command_exists apt; then
    sudo apt-get update
    sudo apt-get install -y jq
  elif command_exists yum; then
    sudo yum install -y jq
  else
    echo "Error: Neither apt nor yum package manager found. Please install jq manually."
    exit 1
  fi
}

# Install jq with Chocolatey on Windows
install_with_choco() {
  if command_exists choco; then
    choco install jq
  else
    echo "Error: Chocolatey is not installed. Please install Chocolatey and try again."
    exit 1
  fi
}

# Main script
install_dependencies