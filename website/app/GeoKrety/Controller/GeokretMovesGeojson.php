<?php

namespace GeoKrety\Controller;

use GeoKrety\Traits\GeokretLoader;

class GeokretMovesGeojson extends Base {
    use GeokretLoader;

    public function get($f3) {
        header('Content-Type: application/json; charset=utf-8');
        $page = $f3->get('PARAMS.page');
        if (!ctype_digit(strval($page))) {
            $page = 1;
        }
        $start = ($page - 1) * GK_PAGINATION_GEOKRET_MOVES;
        $per_page = GK_PAGINATION_GEOKRET_MOVES;

        $sql = <<<EOT
            select json_build_object(
                'type', 'FeatureCollection',
                'features', json_agg(public.ST_AsGeoJSON(t.*)::json)::jsonb || json_agg(public.ST_AsGeoJSON(f.*)::json)::jsonb
            ) AS geojson
            from (select waypoint, position, elevation, distance, country, step,
                coalesce(TRUNC(EXTRACT(EPOCH FROM (moved_on_datetime - lag(moved_on_datetime) over (order by moved_on_datetime DESC)::timestamptz))/60), 0) AS minutes,
                lat, lon,
                moved_on_datetime,
                move_id,
                move_type,
                author, author_username,
                COALESCE (lag(step) over (order by moved_on_datetime DESC), step) AS next_step,
                COALESCE (lag(step) over (order by moved_on_datetime ASC), step) AS previous_step
                from (
                    SELECT gk_moves.id AS move_id, gk_moves.*, COALESCE(gk_users.username, gk_moves.username) AS author_username,
                        ROW_NUMBER() OVER (ORDER BY moved_on_datetime ASC) AS step
                    FROM gk_moves
                    LEFT JOIN gk_users ON author = gk_users.id
                    WHERE geokret=?
                    ORDER BY moved_on_datetime DESC
                    LIMIT ?
                    OFFSET ?
                ) as y
                WHERE y.position is not NULL
            ) as t,
            (SELECT public.ST_MakeLine(position::public.geometry ORDER BY moved_on_datetime DESC) As geom
                FROM (
                    SELECT id, geokret, position, moved_on_datetime
                    FROM gk_moves
                    where geokret=?
                    GROUP BY id
                    ORDER BY moved_on_datetime DESC
                    LIMIT ?
                    OFFSET ?
                ) AS g
                WHERE g.position is not NULL
                GROUP BY g.geokret
            ) as f;
EOT;
        $result = $f3->get('DB')->exec($sql, [$this->geokret->id, $per_page, $start, $this->geokret->id, $per_page, $start]);
        die($result[0]['geojson']);
    }
}
