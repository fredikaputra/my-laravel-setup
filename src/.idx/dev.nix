{ pkgs }:
let
  customPhp = pkgs.php84.buildEnv {
    extensions = ({ enabled, all }: enabled ++ (with all; [
      xdebug
      pcov
      redis
    ]));
    extraConfig = ''
      memory_limit=512M
      error_reporting=E_ALL
      display_errors=On

      realpath_cache_size=4096K
      realpath_cache_ttl=600
        
      opcache.enable=1
      opcache.enable_cli=0
      opcache.memory_consumption=256
      opcache.max_accelerated_files=20000
      opcache.validate_timestamps=1
      opcache.revalidate_freq=0
      opcache.jit=tracing
      opcache.jit_buffer_size=100M

      pcov.enabled=1
      pcov.directory=app

      xdebug.mode=debug
      xdebug.start_with_request=trigger
      xdebug.client_port=9003
      xdebug.client_host=127.0.0.1
    '';
  };
in
{
  channel = "stable-25.05";
  packages = [
    pkgs.systemd
    pkgs.glib
    pkgs.nspr
    pkgs.nss
    pkgs.atk
    pkgs.dbus
    pkgs.expat
    pkgs.xorg.libX11
    pkgs.xorg.libXcomposite
    pkgs.xorg.libXdamage
    pkgs.xorg.libXext
    pkgs.xorg.libXfixes
    pkgs.xorg.libXrandr
    pkgs.xorg.libxcb
    pkgs.libgbm
    pkgs.libxkbcommon
    pkgs.alsa-lib
    pkgs.sqlite
    pkgs.bun
    pkgs.mailpit
    pkgs.meilisearch
    customPhp
    customPhp.packages.composer
  ];
  env = {
    PLAYWRIGHT_SKIP_VALIDATE_HOST_REQUIREMENTS = "true";
  };
  services = {
    redis = {
      enable = true;
    };
    mysql = {
      enable = true;
      package = pkgs.mariadb;
    };
  };
  idx = {
    extensions = [
      "laravel.vscode-laravel"
      "bmewburn.vscode-intelephense-client"
      "xdebug.php-debug"
      "bradlc.vscode-tailwindcss"
    ];
    workspace.onCreate.setup = ".idx/setup.sh";
    previews = {
      enable = true;
      previews = {
        vite = {
          command = [ "bun" "run" "dev" ];
          manager = "web";
        };
        mailpit = {
          command = [ "mailpit" ];
          manager = "web";
        };
        meilisearch = {
          command = [ "meilisearch" ];
          manager = "web";
        };
        web = {
          command = [ "php" "artisan" "serve" "--port" "$PORT" "--host" "0.0.0.0" ];
          manager = "web";
        };
      };
    };
  };
}
