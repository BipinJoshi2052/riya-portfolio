(function () {
  'use strict';

  var points = window.__MAP_POINTS__ || [];
  var PRIMARY_COLOR = window.__PRIMARY_COLOR__ || '#ccff00';
  var KATHMANDU = [85.3240, 27.7172];

  var vectorSource = new ol.source.Vector();

  points.forEach(function (p, i) {
    var feature = new ol.Feature({
      geometry: new ol.geom.Point(ol.proj.fromLonLat([p.lon, p.lat])),
    });
    feature.set('pointIndex', i);
    vectorSource.addFeature(feature);
  });

  var vectorLayer = new ol.layer.Vector({
    source: vectorSource,
    style: new ol.style.Style({
      image: new ol.style.Circle({
        radius: 7,
        fill: new ol.style.Fill({ color: PRIMARY_COLOR }),
        stroke: new ol.style.Stroke({ color: '#0a0a0a', width: 2 }),
      }),
    }),
  });

  var popupEl = document.getElementById('rp-map-popup');
  var popupContent = document.getElementById('rp-map-popup-content');
  var overlay = new ol.Overlay({
    element: popupEl,
    positioning: 'bottom-center',
    offset: [0, -12],
    stopEvent: true,
  });

  var map = new ol.Map({
    target: 'rp-map',
    layers: [
      new ol.layer.Tile({ source: new ol.source.OSM() }),
      vectorLayer,
    ],
    overlays: [overlay],
    view: new ol.View({
      center: ol.proj.fromLonLat(KATHMANDU),
      zoom: 6,
    }),
  });

  if (points.length) {
    var extent = vectorSource.getExtent();
    map.getView().fit(extent, { padding: [60, 60, 60, 60], maxZoom: 14 });
  }

  function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str == null ? '' : String(str);
    return div.innerHTML;
  }

  function showPopup(point, coordinate) {
    popupContent.innerHTML =
      '<div class="title">' + escapeHtml(point.label || 'Unknown location') + '</div>' +
      '<div class="muted">IP: ' + escapeHtml(point.ip) + '</div>' +
      '<div class="muted">' + point.visits + ' visit' + (point.visits === 1 ? '' : 's') + '</div>' +
      '<div class="muted">Last: ' + escapeHtml(point.lastVisit) + '</div>';
    overlay.setPosition(coordinate);
    popupEl.classList.add('is-open');
  }

  function hidePopup() {
    overlay.setPosition(undefined);
    popupEl.classList.remove('is-open');
  }

  map.on('click', function (evt) {
    var found = false;
    map.forEachFeatureAtPixel(evt.pixel, function (feature) {
      var idx = feature.get('pointIndex');
      var point = points[idx];
      if (point) {
        showPopup(point, evt.coordinate);
        found = true;
      }
      return true;
    });
    if (!found) hidePopup();
  });

  map.on('pointermove', function (evt) {
    var hit = map.hasFeatureAtPixel(evt.pixel);
    map.getTargetElement().style.cursor = hit ? 'pointer' : '';
  });

  document.getElementById('rp-map-popup-close').addEventListener('click', hidePopup);
})();
