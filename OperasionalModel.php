<?php
require_once __DIR__ . '/../config/database.php';

class OperasionalModel {

    public function getAll(string $filter_start = '', string $filter_end = ''): array {
        $sql = "SELECT * FROM operasional";
        $params = [];
        if ($filter_start && $filter_end) {
            $sql .= " WHERE tanggal BETWEEN :start AND :end";
            $params = [':start' => $filter_start, ':end' => $filter_end];
        } elseif ($filter_start) {
            $sql .= " WHERE tanggal >= :start";
            $params = [':start' => $filter_start];
        }
        $sql .= " ORDER BY tanggal DESC, created_at DESC";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false {
        $stmt = db()->prepare("SELECT * FROM operasional WHERE id_operasional = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create(string $tanggal, float $nominal, string $keterangan): bool {
        $stmt = db()->prepare(
            "INSERT INTO operasional (tanggal, nominal_operasional, keterangan) VALUES (:tanggal, :nominal, :keterangan)"
        );
        return $stmt->execute([':tanggal' => $tanggal, ':nominal' => $nominal, ':keterangan' => $keterangan]);
    }

    public function update(int $id, string $tanggal, float $nominal, string $keterangan): bool {
        $stmt = db()->prepare(
            "UPDATE operasional SET tanggal=:tanggal, nominal_operasional=:nominal, keterangan=:keterangan WHERE id_operasional=:id"
        );
        return $stmt->execute([':tanggal' => $tanggal, ':nominal' => $nominal, ':keterangan' => $keterangan, ':id' => $id]);
    }

    public function delete(int $id): bool {
        $stmt = db()->prepare("DELETE FROM operasional WHERE id_operasional = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getSummary(string $filter_start = '', string $filter_end = ''): array {
        $where = '';
        $params = [];
        if ($filter_start && $filter_end) {
            $where = " WHERE tanggal BETWEEN :start AND :end";
            $params = [':start' => $filter_start, ':end' => $filter_end];
        }
        $stmt = db()->prepare("SELECT COALESCE(SUM(nominal_operasional), 0) AS total_operasional FROM operasional" . $where);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function getToday(): array {
        $stmt = db()->prepare("SELECT COALESCE(SUM(nominal_operasional), 0) AS total FROM operasional WHERE tanggal = CURDATE()");
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getChartData(int $days = 7): array {
        $stmt = db()->prepare(
            "SELECT tanggal, COALESCE(SUM(nominal_operasional), 0) AS total
             FROM operasional
             WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
             GROUP BY tanggal ORDER BY tanggal ASC"
        );
        $stmt->execute([':days' => $days]);
        return $stmt->fetchAll();
    }
}
