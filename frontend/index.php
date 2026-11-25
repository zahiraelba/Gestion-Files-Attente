<?php
require_once 'backend/config/db.php';
// Fetch active services
$stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1");
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borne d'Accueil - FileFlow</title>
    <style>
        
        :root {
            --dark-blue: #1E1B4B;
            --primary-blue: #4338CA;
            --light-blue: #6366F1;
            --pale-blue: #A5B4FC;
            --bg-blue: #E0E7FF;
            --white: #F8FAFF;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        body { 
            background: linear-gradient(135deg, var(--bg-blue), var(--white)); 
            height: 100vh; 
            display: flex; 
            flex-direction: column; 
            color: var(--dark-blue);
        }

        /* Header */
        .header { 
            background-color: var(--dark-blue); 
            color: var(--white); 
            padding: 20px 40px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .header h1 { font-size: 28px; font-weight: bold; }

        /* Main Layout */
        .main-container {
            display: flex;
            flex-grow: 1;
            padding: 40px;
            gap: 40px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        /* Left Side: Service Selection */
        .services-section {
            flex: 2;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .services-section h2 { font-size: 24px; margin-bottom: 10px; }
        
        .services-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .service-btn {
            background: var(--white);
            border: 2px solid var(--pale-blue);
            border-radius: 15px;
            padding: 40px 20px;
            font-size: 22px;
            font-weight: bold;
            color: var(--primary-blue);
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
        .service-btn:hover {
            background: var(--bg-blue);
            transform: translateY(-5px);
            border-color: var(--light-blue);
            box-shadow: 0 10px 20px rgba(67, 56, 202, 0.2);
        }
        .service-btn svg { width: 40px; height: 40px; fill: var(--light-blue); }

        /* Right Side: Live Status */
        .status-section {
            flex: 1;
            background: var(--white);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            border-top: 8px solid var(--primary-blue);
        }
        .status-section h2 { font-size: 20px; color: #666; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px; }
        
        .current-ticket-box {
            background: var(--bg-blue);
            width: 100%;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .current-ticket-label { font-size: 16px; color: var(--primary-blue); font-weight: bold; margin-bottom: 10px; }
        .current-ticket-number { font-size: 80px; font-weight: 900; color: var(--dark-blue); line-height: 1; }
        .current-counter { font-size: 18px; color: #666; margin-top: 10px; }

        .waiting-box {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 18px;
            color: var(--dark-blue);
            font-weight: bold;
        }
        .waiting-count { font-size: 24px; color: var(--light-blue); }

        /* Modal for Ticket */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(30, 27, 75, 0.8);
            display: none; justify-content: center; align-items: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
        }
        .modal-content {
            background: var(--white);
            padding: 60px;
            border-radius: 20px;
            text-align: center;
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            max-width: 500px;
            width: 90%;
        }
        .modal-content h2 { color: #666; font-size: 24px; margin-bottom: 20px; }
        .modal-ticket { font-size: 120px; font-weight: 900; color: var(--primary-blue); line-height: 1; margin: 20px 0; }
        .modal-close {
            background: var(--dark-blue); color: white; border: none;
            padding: 15px 40px; border-radius: 10px; font-size: 18px; font-weight: bold;
            cursor: pointer; margin-top: 20px; width: 100%;
        }

        @keyframes popIn {
            0% { transform: scale(0.5); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Responsive */
        @media (max-width: 900px) {
            .main-container { flex-direction: column; }
            .services-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>FileFlow</h1>
        <div style="font-size: 18px;" id="clock">--:--</div>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        
        <!-- Left: Services -->
        <div class="services-section">
            <h2>Bienvenue. Veuillez choisir un service :</h2>
            <div class="services-grid">
                <?php foreach($services as $s): ?>
                    <button class="service-btn" onclick="generateTicket(<?= $s['id'] ?>)">
                        <!-- Simple SVG Icon -->
                        <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                        <?= htmlspecialchars($s['name']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right: Live Status -->
        <div class="status-section">
            <h2>En cours d'appel</h2>
            
            <div class="current-ticket-box">
                <div class="current-ticket-label">TICKET ACTUEL</div>
                <div class="current-ticket-number" id="currentTicket">--</div>
                <div class="current-counter" id="currentCounter">Guichet --</div>
            </div>

            <div class="waiting-box">
                <span>Personnes en attente :</span>
                <span class="waiting-count" id="waitingCount">0</span>
            </div>
        </div>

    </div>

    <!-- Ticket Modal -->
    <div class="modal-overlay" id="ticketModal">
        <div class="modal-content">
            <h2>Votre ticket est prêt</h2>
            <div class="modal-ticket" id="modalTicketNumber">A-000</div>
            <p style="color: #666; margin-bottom: 20px;">Veuillez surveiller l'écran pour votre appel.</p>
            <button class="modal-close" onclick="closeModal()">Terminer</button>
        </div>
    </div>

    <script>
        // 1. Live Clock
        setInterval(() => {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'});
        }, 1000);

        // 2. Generate Ticket
        function generateTicket(serviceId) {
            const formData = new FormData();
            formData.append('service_id', serviceId);
            
            fetch('../backend/api/ticket.php?action=generate', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    // Show Modal
                    document.getElementById('modalTicketNumber').innerText = data.ticket;
                    document.getElementById('ticketModal').style.display = 'flex';
                    updateStatus(); // Refresh queue immediately
                } else {
                    alert("Erreur: " + data.error);
                }
            });
        }

        // 3. Close Modal
        function closeModal() {
            document.getElementById('ticketModal').style.display = 'none';
        }

        // 4. Live Status Polling (Every 3 seconds)
        function updateStatus() {
            fetch('../backend/api/ticket.php?action=status')
            .then(res => res.json())
            .then(data => {
                if(data.current) {
                    document.getElementById('currentTicket').innerText = data.current.ticket_number;
                    document.getElementById('currentCounter').innerText = "Guichet " + data.current.counter_number;
                } else {
                    document.getElementById('currentTicket').innerText = "--";
                    document.getElementById('currentCounter').innerText = "Guichet --";
                }
                document.getElementById('waitingCount').innerText = data.waiting;
            })
            .catch(err => console.log("Erreur de connexion au serveur"));
        }

        // Start polling
        updateStatus();
        setInterval(updateStatus, 3000);
    </script>

</body>
</html>
