get_github_latest_version() {
  VERSION=$(curl --silent "https://api.github.com/repos/$1/releases/latest" | grep '"tag_name":' | sed -E 's/.*"([^"]+)".*/\1/');
}

get_architecture() {
  case "$(uname -m)" in
    'i386' | 'i686')
      MACHINE='i386'
      ;;
    'amd64' | 'x86_64')
      MACHINE='amd64'
      ;;
    'armv7' | 'armv7l')
      MACHINE='arm'
      ;;
    'armv8' | 'aarch64')
      MACHINE='arm64'
      ;;
    *)
      echo "The architecture is not supported."
      exit 1
      ;;
  esac
}

VLMCSD_DIR="/tmp/vlmcsd"
PKG_DIR="$VLMCSD_DIR/pkg"
mkdir -p $PKG_DIR

get_architecture
case "$MACHINE" in
  'i386')
    VLMCSD_PATH="binaries/Linux/intel/static/vlmcsd-x86-musl-static"
    ;;
  'amd64')
    VLMCSD_PATH="binaries/Linux/intel/static/vlmcsd-x64-musl-static"
    ;;
  'arm')
    VLMCSD_PATH="binaries/Linux/arm/little-endian/static/vlmcsd-armv7el-uclibc-static"
    ;;
  'arm64')
    VLMCSD_PATH="binaries/Linux/arm/little-endian/static/vlmcsd-armv7el-uclibc-static"
    ;;
  *)
    exit 1
    ;;
esac

get_github_latest_version "Wind4/vlmcsd"
wget -P $PKG_DIR "https://github.com/Wind4/vlmcsd/releases/download/$VERSION/binaries.tar.gz"
tar xf $PKG_DIR/binaries.tar.gz -C $PKG_DIR
mv $PKG_DIR/$VLMCSD_PATH $VLMCSD_DIR/vlmcsd

rm -rf $PKG_DIR