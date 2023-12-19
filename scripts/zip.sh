#!/bin/bash

plugin_path="$PWD"
version=$(jq -r .version "$plugin_path/package.json")
package_name=$(jq -r .name "$plugin_path/package.json")
output_dir="$plugin_path/releases"
output_file="$package_name-v$version.zip"

# Create a temporary directory for the files
temp_dir=$(mktemp -d)

# Copy files to the temporary directory
cp -r "$plugin_path/build"/* "$temp_dir"
cp -r "$plugin_path/src/includes" "$temp_dir"
cp "$plugin_path/src/$package_name.php" "$temp_dir"

# Navigate to the temporary directory
cd "$temp_dir" || exit

# Zip all files
mkdir $plugin_path/releases
zip -r $plugin_path/releases/"$output_file" .

# Clean up: Remove the temporary directory
rm -rf "$temp_dir"

echo "ZIP file created at: $output_dir/$output_file"