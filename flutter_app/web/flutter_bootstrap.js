{{flutter_js}}
{{flutter_build_config}}

(function () {
  const builds = _flutter.buildConfig?.builds || [];
  const hasSkwasmBuild = builds.some((build) => build.renderer === 'skwasm');

  const config = hasSkwasmBuild
    ? {
        renderer: 'skwasm',
        forceSingleThreadedSkwasm: true,
      }
    : {};

  _flutter.loader.load({
    config,
  });
})();
