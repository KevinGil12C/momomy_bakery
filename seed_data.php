<?php

/**
 * Momomy Bakery - Seed Script
 * Inserta datos de prueba: clientes, comentarios, pedidos, noticias, suscriptores
 * 
 * Contraseña de prueba para todos los clientes: Test1234
 * Contraseña de prueba admin: admin123
 */

try {
    $db = new PDO('mysql:host=localhost;dbname=momomy_bakery', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $password = password_hash('Test1234', PASSWORD_DEFAULT);
    $adminPass = password_hash('admin123', PASSWORD_DEFAULT);

    echo "=== Momomy Bakery Seed Script ===\n\n";

    // ========================================
    // 1. CLIENTES DE PRUEBA
    // ========================================
    echo "[1/6] Insertando clientes de prueba...\n";

    $customers = [
        ['María', 'González', 'maria@prueba.com', '5551001001'],
        ['Carlos', 'Hernández', 'carlos@prueba.com', '5551002002'],
        ['Lucía', 'Martínez', 'lucia@prueba.com', '5551003003'],
        ['Fernando', 'Díaz', 'fernando@prueba.com', '5551004004'],
        ['Valentina', 'Rojas', 'valentina@prueba.com', '5551005005'],
    ];

    $stmt = $db->prepare("INSERT IGNORE INTO customers (first_name, last_name, email, password, phone) VALUES (?, ?, ?, ?, ?)");
    foreach ($customers as $c) {
        $stmt->execute([$c[0], $c[1], $c[2], $password, $c[3]]);
    }
    echo "   ✔ 5 clientes insertados (contraseña: Test1234)\n";

    // Obtener IDs de clientes recién insertados
    $customerIds = $db->query("SELECT id FROM customers ORDER BY id ASC LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);

    // ========================================
    // 2. COMENTARIOS Y RATINGS DE PRUEBA
    // ========================================
    echo "[2/6] Insertando comentarios y calificaciones...\n";

    $comments = [
        // Product 1 - Pastel de Chocolate Premium
        [1, $customerIds[0] ?? 1, '¡Increíblemente delicioso! El mejor pastel de chocolate que he probado en mi vida. La cobertura de ganache es perfección pura.', 5],
        [1, $customerIds[1] ?? 2, 'Muy bueno, aunque me hubiera gustado un poco más de relleno. La textura es suave y húmeda.', 4],
        [1, $customerIds[2] ?? 3, 'Lo pedí para el cumpleaños de mi hija y todos quedaron encantados. Definitivamente lo vuelvo a pedir.', 5],
        [1, $customerIds[3] ?? 4, 'Buen sabor pero la porción me pareció un poco pequeña para el precio. Aun así, calidad premium.', 3],

        // Product 2 - Cupcakes de Vainilla
        [2, $customerIds[0] ?? 1, 'Están monísimos y saben delicioso. Perfectos para llevar a la oficina.', 5],
        [2, $customerIds[4] ?? 5, 'Ricos pero demasiado dulces para mi gusto. La decoración es muy linda.', 3],
        [2, $customerIds[2] ?? 3, 'Mis hijos los amaron, la vainilla se siente natural y no artificial. ¡Excelente!', 4],

        // Product 3 - Tarta de Frutos Rojos
        [3, $customerIds[1] ?? 2, 'Una explosión de sabores frescos. La combinación de fresas y frambuesas es genial.', 5],
        [3, $customerIds[3] ?? 4, 'Hermosa presentación y sabor balance ado entre dulce y ácido. Me encantó.', 4],
        [3, $customerIds[4] ?? 5, 'La mejor tarta que he probado en esta zona. La recomiendo al 100%.', 5],

        // Product 4 - Galletas de Chispas
        [4, $customerIds[0] ?? 1, 'Crujientes por fuera, suaves por dentro. ¡Justo como me gustan! Adictivas.', 5],
        [4, $customerIds[2] ?? 3, 'Buenas pero no superan a las de mi abuela jaja. Aun así muy ricas.', 4],
        [4, $customerIds[1] ?? 2, 'Compré dos veces en la misma semana, eso dice todo sobre estas galletas.', 5],

        // Product 5 - Cheesecake de New York
        [5, $customerIds[3] ?? 4, 'Cremoso, suave y con una base de galleta perfecta. Auténtico estilo New York.', 5],
        [5, $customerIds[4] ?? 5, 'Demasiado denso para mi gusto, pero el sabor es muy bueno. Se nota la calidad.', 3],
        [5, $customerIds[0] ?? 1, '¡Lo AMO! Es mi postre favorito de toda la tienda. Nunca me canso de pedirlo.', 5],
    ];

    $stmt = $db->prepare("INSERT INTO product_comments (product_id, user_id, comment, rating) VALUES (?, ?, ?, ?)");
    foreach ($comments as $c) {
        $stmt->execute($c);
    }
    echo "   ✔ 16 comentarios con ratings insertados\n";

    // ========================================
    // 3. PEDIDOS VINCULADOS A CLIENTES
    // ========================================
    echo "[3/6] Insertando pedidos de clientes...\n";

    $orders = [
        [$customerIds[0] ?? 1, 'María', 'González', 'maria@prueba.com', '5551001001', 900.00, 'completed', 'paid', 'Entregar en la puerta del edificio B', [[1, 2, 450.00]]],
        [$customerIds[1] ?? 2, 'Carlos', 'Hernández', 'carlos@prueba.com', '5551002002', 500.00, 'processing', 'paid', 'Para fiesta de oficina', [[3, 1, 320.00], [2, 1, 180.00]]],
        [$customerIds[2] ?? 3, 'Lucía', 'Martínez', 'lucia@prueba.com', '5551003003', 380.00, 'pending', 'unpaid', '', [[5, 1, 380.00]]],
        [$customerIds[3] ?? 4, 'Fernando', 'Díaz', 'fernando@prueba.com', '5551004004', 240.00, 'completed', 'paid', 'Sin azúcar extra por favor', [[4, 2, 120.00]]],
        [$customerIds[4] ?? 5, 'Valentina', 'Rojas', 'valentina@prueba.com', '5551005005', 1350.00, 'processing', 'paid', 'Evento corporativo, entregar antes de las 10AM', [[1, 3, 450.00]]],
        [$customerIds[0] ?? 1, 'María', 'González', 'maria@prueba.com', '5551001001', 640.00, 'pending', 'unpaid', 'Segunda compra, ahora quiero de frutos rojos', [[3, 2, 320.00]]],
    ];

    $orderStmt = $db->prepare("INSERT INTO orders (customer_id, customer_first_name, customer_last_name, customer_email, customer_phone, total_amount, status, payment_status, notes, tracking_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");

    foreach ($orders as $o) {
        $token = md5(uniqid(rand(), true));
        $items = array_pop($o);
        $o[] = $token;
        $orderStmt->execute($o);
        $orderId = $db->lastInsertId();

        foreach ($items as $item) {
            $itemStmt->execute([$orderId, $item[0], $item[1], $item[2]]);
        }
    }
    echo "   ✔ 6 pedidos con items insertados\n";

    // ========================================
    // 4. NOTICIAS DE PRUEBA
    // ========================================
    echo "[4/6] Insertando noticias...\n";

    $news = [
        ['🎂 ¡Nuevo Sabor! Pastel Red Velvet ya disponible', 'Hemos agregado a nuestro menú el clásico Red Velvet con cream cheese frosting. Un sabor aterciopelado que combina lo mejor del cacao con un toque único. Disponible en porciones individuales y pastel completo. ¡Ven a probarlo antes de que se agoten!', 1],
        ['🍪 Taller de Galletas Decoradas - Sábado 8 de Marzo', 'Te invitamos a nuestro taller especial donde aprenderás a decorar galletas con royal icing. Incluye todos los materiales, 12 galletas para llevar y un delicioso café artesanal. Cupo limitado a 15 personas. Reserva tu lugar llamando al 5512345678.', 1],
        ['💕 Promo San Valentín Extendida', 'Por demanda popular, extendemos nuestra promoción de San Valentín todo el mes de marzo: 2x1 en cupcakes de vainilla y 20% de descuento en pasteles de chocolate para parejas. ¡El amor sabe mejor con Momomy!', 1],
    ];

    $stmt = $db->prepare("INSERT INTO news (title, content, is_published) VALUES (?, ?, ?)");
    foreach ($news as $n) {
        $stmt->execute($n);
    }
    echo "   ✔ 3 noticias insertadas\n";

    // ========================================
    // 5. SUSCRIPTORES AL NEWSLETTER
    // ========================================
    echo "[5/6] Insertando suscriptores al newsletter...\n";

    $subs = [
        'maria@prueba.com',
        'carlos@prueba.com',
        'lucia@prueba.com',
        'ana.belen@correo.com',
        'diego.flores@empresa.mx',
        'sofia.martinez@gmail.com',
        'pedro.navaja@hotmail.com',
        'camila.reyes@outlook.com'
    ];

    $stmt = $db->prepare("INSERT IGNORE INTO newsletter_subscribers (email) VALUES (?)");
    foreach ($subs as $email) {
        $stmt->execute([$email]);
    }
    echo "   ✔ 8 suscriptores insertados\n";

    // ========================================
    // 6. CONTACTOS ADICIONALES
    // ========================================
    echo "[6/6] Insertando mensajes de contacto...\n";

    $contacts = [
        ['Roberto Gómez', 'roberto@test.com', '¿Hacen pasteles sin gluten? Mi esposa es celíaca y me encantaría sorprenderla.'],
        ['Diana Flores', 'diana.f@mail.com', 'Quisiera cotizar una mesa de postres para 80 personas, es para una boda en junio.'],
        ['Arturo Vidal', 'arturo@empresa.com', '¿Tienen servicio de entrega a domicilio los domingos? Necesito para un evento familiar.'],
    ];

    $stmt = $db->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
    foreach ($contacts as $ct) {
        $stmt->execute($ct);
    }
    echo "   ✔ 3 mensajes de contacto insertados\n";

    // ========================================
    // RESUMEN
    // ========================================
    echo "\n===================================\n";
    echo "  ✅ SEED COMPLETADO CON ÉXITO\n";
    echo "===================================\n\n";
    echo "CREDENCIALES DE PRUEBA:\n";
    echo "────────────────────────────────\n";
    echo "👤 Cliente: maria@prueba.com\n";
    echo "🔑 Pass:    Test1234\n";
    echo "────────────────────────────────\n";
    echo "(Todos los clientes usan Test1234)\n\n";
    echo "📊 Resumen:\n";
    echo "  • 5 clientes registrados\n";
    echo "  • 16 reseñas con calificaciones\n";
    echo "  • 6 pedidos con productos\n";
    echo "  • 3 noticias publicadas\n";
    echo "  • 8 suscriptores al newsletter\n";
    echo "  • 3 mensajes de contacto\n";
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
