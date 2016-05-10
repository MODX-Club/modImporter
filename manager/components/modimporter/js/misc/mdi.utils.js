modImporter.utils.renderActions = function(value, props, row) {
  var res = [];
  var cls,
    icon,
    title,
    action,
    item = '';
  for (var i in row.data.actions) {
    if (!row.data.actions.hasOwnProperty(i)) {
      continue;
    }
    var a = row.data.actions[i];
    if (!a['button']) {
      continue;
    }

    cls = a['cls'] ? a['cls'] : '';
    icon = a['icon'] ? a['icon'] : '';
    action = a['action'] ? a['action'] : '';
    title = a['title'] ? a['title'] : '';

    item = String.format(
      '<li class="{0}"><button class="btn btn-default {1}" action="{2}" title="{3}">{3}</button></li>',
      cls, icon, action, title
    );

    res.push(item);
  }

  return String.format(
    '<ul class="modimporter-row-actions">{0}</ul>',
    res.join('')
  );
};
