<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AlunniController
{
    private $conn;

    // Costruttore: crea la connessione UNA sola volta
    public function __construct()
    {
        $this->conn = new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');

        if ($this->conn->connect_error) {
            die("Errore connessione: " . $this->conn->connect_error);
        }
    }

    // =========================
    // GET /alunni
    // =========================
    public function index(Request $request, Response $response, $args)
    {
        $result = $this->conn->query("SELECT * FROM alunni");
        $results = $result->fetch_all(MYSQLI_ASSOC);

        $response->getBody()->write(json_encode($results));
        return $response
            ->withHeader("Content-Type", "application/json")
            ->withStatus(200);
    }

    // =========================
    // GET /alunni/{id}
    // =========================
    public function show(Request $request, Response $response, $args)
    {
        $id = $args['id'];

        $stmt = $this->conn->prepare("SELECT * FROM alunni WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $alunno = $result->fetch_assoc();

        if ($alunno) {
            $response->getBody()->write(json_encode($alunno));
            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(["message" => "Alunno non trovato"]));
            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(404);
        }
    }

    // =========================
    // POST /alunni
    // =========================
    public function create(Request $request, Response $response, $args)
    {
        $data = json_decode($request->getBody()->getContents(), true);

        $stmt = $this->conn->prepare(
            "INSERT INTO alunni (nome, cognome, email, data_nascita) VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssss",
            $data['nome'],
            $data['cognome'],
            $data['email'],
            $data['data_nascita']
        );

        if ($stmt->execute()) {
            $response->getBody()->write(json_encode(["message" => "Alunno creato"]));
            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(201);
        } else {
            $response->getBody()->write(json_encode(["message" => "Errore creazione"]));
            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(500);
        }
    }

    // =========================
    // PUT /alunni/{id}
    // =========================
    public function update(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $data = json_decode($request->getBody()->getContents(), true);

        $stmt = $this->conn->prepare(
            "UPDATE alunni 
             SET nome = ?, cognome = ?, email = ?, data_nascita = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "ssssi",
            $data['nome'],
            $data['cognome'],
            $data['email'],
            $data['data_nascita'],
            $id
        );

        if ($stmt->execute()) {
            $response->getBody()->write(json_encode(["message" => "Alunno aggiornato"]));
            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(["message" => "Errore aggiornamento"]));
            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(500);
        }
    }

    // =========================
    // DELETE /alunni/{id}
    // =========================
    public function destroy(Request $request, Response $response, $args)
    {
        $id = $args['id'];

        $stmt = $this->conn->prepare("DELETE FROM alunni WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $response->getBody()->write(json_encode(["message" => "Alunno eliminato"]));
            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(["message" => "Errore eliminazione"]));
            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(500);
        }
    }
}
