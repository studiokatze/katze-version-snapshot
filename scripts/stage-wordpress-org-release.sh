#!/bin/sh

set -eu

SCRIPT_DIR=$(CDPATH= cd -- "$(dirname "$0")" && pwd)
REPO_DIR=$(CDPATH= cd -- "$SCRIPT_DIR/.." && pwd)

if [ "$#" -ne 1 ]; then
    echo "Usage: $0 /path/to/svn-working-copy" >&2
    exit 1
fi

SVN_DIR=$1
TRUNK_DIR=$SVN_DIR/trunk
TAGS_DIR=$SVN_DIR/tags
ASSETS_DIR=$SVN_DIR/assets

if [ ! -d "$SVN_DIR/.svn" ]; then
    echo "SVN working copy not found: $SVN_DIR" >&2
    exit 1
fi

if [ ! -d "$TRUNK_DIR" ] || [ ! -d "$TAGS_DIR" ] || [ ! -d "$ASSETS_DIR" ]; then
    echo "Expected directories missing under SVN working copy: trunk, tags, assets" >&2
    exit 1
fi

VERSION=$(sed -n "s/^ \* Version: //p" "$REPO_DIR/katze-version-snapshot.php" | head -n 1)
if [ -z "$VERSION" ]; then
    echo "Failed to detect plugin version from katze-version-snapshot.php" >&2
    exit 1
fi

TAG_DIR=$TAGS_DIR/$VERSION

mkdir -p "$TAG_DIR"

rsync -a --delete \
    --exclude '.git/' \
    --exclude '.gitignore' \
    --exclude '.DS_Store' \
    --exclude '.build/' \
    --exclude 'docs/' \
    --exclude 'scripts/' \
    --exclude 'README.md' \
    --exclude 'katze-version-snapshot.zip' \
    --exclude 'katze-version-snapshot/' \
    "$REPO_DIR/" "$TRUNK_DIR/"

rsync -a --delete "$TRUNK_DIR/" "$TAG_DIR/"

if [ -d "$REPO_DIR/wordpress-org-assets" ]; then
    rsync -a --delete \
        --exclude '.DS_Store' \
        "$REPO_DIR/wordpress-org-assets/" "$ASSETS_DIR/"
fi

echo "Staged version $VERSION into:"
echo "  trunk: $TRUNK_DIR"
echo "  tag:   $TAG_DIR"
if [ -d "$REPO_DIR/wordpress-org-assets" ]; then
    echo "  assets: $ASSETS_DIR"
fi
echo
echo "Next steps:"
echo "  svn status \"$SVN_DIR\""
echo "  svn add --force \"$SVN_DIR/trunk\" \"$SVN_DIR/tags/$VERSION\" \"$SVN_DIR/assets\""
echo "  svn commit -m \"Release $VERSION\" \"$SVN_DIR\""
