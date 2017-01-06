<?php

require 'vendor/autoload.php';

use Docker\Docker;

$docker = new Docker();
$containers = $docker->getContainerManager()->findAll();

$runningContainers = [];
$categories = [];
$hasServiceWithoutCategory = false;
foreach ($containers as $container) {
    $labels = $container->getLabels();
    if (isset($labels['baboard.container.disabled'])) {
        continue;
    }

    foreach ($container->getPorts() as $port) {
        if (null === ($publicPort = $port->getPublicPort())) {
            continue;
        }

        if (isset($labels['baboard.container.name'])) {
            $name = $labels['baboard.container.name'];
        } elseif (isset($labels['com.docker.compose.service'])) {
            $name = $labels['com.docker.compose.service'];
        } else {
            $name = null;
        }

        if (null === $name) {
            continue;
        }

        if (isset($labels['baboard.container.default_path'])) {
            $defaultPath = $labels['baboard.container.default_path'];
        } else {
            $defaultPath = null;
        }


        if (isset($labels['baboard.project.name'])) {
            $project = $labels['baboard.project.name'];
        } elseif (isset($labels['com.docker.compose.project'])) {
            $project = $labels['com.docker.compose.project'];
        } else {
            $project = null;
        }

        $category = null;
        if (isset($labels['baboard.project.category'])) {
            $category = $labels['baboard.project.category'];
        }

        if (null === $category) {
            $hasServiceWithoutCategory = true;
        } else {
            $categories[$category] = $category;
        }

        $runningContainers[] = [
            'service'      => $name,
            'project'      => $project,
            'port'         => $port->getPublicPort(),
            'category'     => $category,
            'default_path' => $defaultPath,
        ];
    }
}

$sortFlags = SORT_FLAG_CASE + SORT_STRING;

$preparedInfos = [];
foreach ($runningContainers as $infos) {
    $preparedInfos[$infos['project']][$infos['service']] = [
        'port' => $infos['port'],
        'category' => $infos['category'],
        'default_path' => $infos['default_path'],
    ];
    ksort($preparedInfos[$infos['project']], $sortFlags);
}
ksort($preparedInfos, $sortFlags);
ksort($categories, $sortFlags);
?>
<html>
    <head>
        <meta charset="utf-8">
        <title>Baboard</title>
        <link rel="stylesheet" href="vendor/bower-asset/foundation-sites/dist/css/foundation.css">
        <style>
            body {
                padding: 20px 0 0 0;
            }
            table {
                border-collapse:collapse;
                border-spacing: 0;
            }
            table tbody td,
            table thead th {
                border: 1px solid #ccc;
            }
        </style>
    </head>
    <body>
        <?php if (0 === count($preparedInfos)): ?>
            <div style="text-align: center;">
                Nothing to display.
            </div>
        <?php else: ?>
        <div class="row" style="max-width: none">
            <div class="large-2 columns">
                <ul class="menu vertical">
                    <li class="menu-text">Categories</li>
                    <li class="active"><a href="#" data-link-all class="category-link">All</a></li>
                    <?php if ($hasServiceWithoutCategory): ?>
                        <li><a href="#" data-link-none class="category-link">None</a></li>
                    <?php endif; ?>
                    <?php foreach ($categories as $category): ?>
                        <li><a href="#" class="category-link" data-category-id="<?php echo $category ?>"><?php echo $category ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="large-10 columns">
                <table class="unstriped">
                    <?php foreach ($preparedInfos as $project => $projectInfos): ?>
                        <thead data-project-id="<?php echo $project ?>">
                            <tr>
                                <th colspan="2" style="text-align: center">
                                    <?php echo $project ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody data-project-id="<?php echo $project ?>">
                        <?php foreach ($projectInfos as $service => $serviceInfos): ?>
                            <tr style="cursor: pointer"
                                <?php if (isset($serviceInfos['category'])): ?> data-category-id="<?php echo $serviceInfos['category'] ?>"<?php endif ?>
                            >
                                <td>
                                    <a href="http://localhost:<?php echo $serviceInfos['port'] ?><?php if (null !== $serviceInfos['default_path']): ?><?php echo $serviceInfos['default_path'] ?><?php endif ?>" target="_blank"><?php echo $service ?></a>
                                </td>
                                <td style="text-align: right">
                                    <?php echo $serviceInfos['port'] ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    <?php endforeach; ?>
                </table>
                <script src="vendor/bower-asset/jquery/dist/jquery.js"></script>
                <script>
                    $("tbody tr").click(function(e) {
                        if (e.target.nodeName == 'A') {
                            return;
                        }
                        window.open($(this).find("a").attr("href"), '_blank');
                    });

                    $('.category-link').click(function() {
                        $('.menu li').removeClass('active');
                        var link = $(this);
                        var categoryId = link.data('category-id');
                        link.parent('li').addClass('active');
                        $('tbody tr').each(function() {
                            var displayed;
                            if (typeof link.data('link-all') != 'undefined') {
                                displayed = true;
                            } else if (typeof link.data('link-none') != 'undefined') {
                                displayed = typeof $(this).data('category-id') == 'undefined';
                            } else {
                                displayed = $(this).data('category-id') == categoryId;
                            }

                            $(this).toggle(displayed);
                        });

                        $('tbody').each(function() {
                            var tbody = $(this);
                            var projectId = tbody.data('project-id');
                            $('thead')
                                .filter(function() {
                                    return $(this).data('project-id') == projectId;
                                })
                                .toggle(
                                    $('tr:hidden', tbody).length != $('tr', tbody).length
                                )
                            ;
                        });
                    });
                </script>
            </div>
        </div>
        <?php endif ?>
    </body>
</html>
