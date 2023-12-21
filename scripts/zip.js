const fs = require("fs");
const path = require("path");
const os = require("os");
const { execSync } = require("child_process");
const archiver = require("archiver");

const pluginPath = process.cwd();
const packageJson = require(path.join(pluginPath, "package.json"));
const version = packageJson.version;
const packageName = packageJson.name;
const outputDir = path.join(pluginPath, "releases");
const outputFile = `${packageName}-v${version}.zip`;

// Create a temporary directory for the files
const tempDir = fs.mkdtempSync(path.join(os.tmpdir(), "temp-"));

// Copy files to the temporary directory
execSync(`cp -r ${path.join(pluginPath, "build")}/* ${tempDir}`);
execSync(`cp -r ${path.join(pluginPath, "src", "includes")} ${tempDir}`);
execSync(`cp ${path.join(pluginPath, "src", `${packageName}.php`)} ${tempDir}`);

// Create output directory if it doesn't exist
if (!fs.existsSync(outputDir)) {
	fs.mkdirSync(outputDir);
}

// Zip all files
const output = fs.createWriteStream(path.join(outputDir, outputFile));
const archive = archiver("zip", {
	zlib: { level: 9 }, // Sets the compression level.
});

archive.directory(tempDir, false);
archive.pipe(output);
archive.finalize();

// Clean up: Remove the temporary directory
output.on("close", () => {
	execSync(`rm -rf ${tempDir}`);
	console.log(`ZIP file created at: ${path.join(outputDir, outputFile)}`);
});
