<?php
// sms_templates.php
require_once 'config/database.php';
require_once 'config/functions.php';
require_once 'includes/SmsTemplateEngine.php';
require_login();

$pageTitle = 'SMS Templates';
$currentPage = 'sms_templates';

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_template'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $id = $_POST['template_id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $body = trim($_POST['template_body'] ?? '');
    $alertType = $_POST['alert_type'] ?? 'all';
    $isDefault = isset($_POST['is_default']) ? 1 : 0;

    if (!empty($name) && !empty($body)) {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE sms_templates SET name = ?, template_body = ?, alert_type = ?, is_default = ? WHERE id = ?");
            $stmt->execute([$name, $body, $alertType, $isDefault, $id]);
            log_audit($pdo, 'SMS Template Updated', "Template: $name");
        } else {
            $stmt = $pdo->prepare("INSERT INTO sms_templates (name, template_body, alert_type, is_default) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $body, $alertType, $isDefault]);
            log_audit($pdo, 'SMS Template Created', "Template: $name");
        }
    }
    header("Location: sms_templates.php");
    exit();
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_template'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $pdo->prepare("DELETE FROM sms_templates WHERE id = ?")->execute([$_POST['template_id']]);
    log_audit($pdo, 'SMS Template Deleted');
    header("Location: sms_templates.php");
    exit();
}

$stmt = $pdo->query("SELECT * FROM sms_templates ORDER BY is_default DESC, name ASC");
$templates = $stmt->fetchAll();
$variables = SmsTemplateEngine::getAvailableVariables();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="actions-bar">
    <div class="actions-left">
        <span class="text-muted"><?= count($templates) ?> template(s)</span>
    </div>
    <div class="actions-right">
        <button class="btn btn-primary" onclick="openTemplateModal()">
            <i class="fa-solid fa-plus"></i> New Template
        </button>
    </div>
</div>

<!-- Available Variables -->
<div class="panel">
    <div class="panel-header"><h2><i class="fa-solid fa-code"></i> Available Variables</h2></div>
    <div class="variables-grid">
        <?php foreach ($variables as $var => $desc): ?>
        <div class="variable-chip" onclick="insertVariable('<?= $var ?>')">
            <code><?= $var ?></code>
            <span><?= h($desc) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Templates List -->
<?php if (empty($templates)): ?>
    <div class="panel"><div class="empty-state"><i class="fa-solid fa-message"></i><h3>No templates yet</h3><p>Create your first SMS template.</p></div></div>
<?php else: ?>
    <div class="templates-grid">
        <?php foreach ($templates as $tpl): ?>
        <div class="template-card">
            <div class="template-card-header">
                <h3><?= h($tpl['name']) ?></h3>
                <div class="template-badges">
                    <?php if ($tpl['is_default']): ?><span class="badge badge-primary">Default</span><?php endif; ?>
                    <span class="badge badge-muted"><?= h($tpl['alert_type']) ?></span>
                </div>
            </div>
            <div class="template-body">
                <p class="template-raw"><?= h($tpl['template_body']) ?></p>
                <div class="template-preview">
                    <strong>Preview:</strong>
                    <p><?= h(SmsTemplateEngine::preview($tpl['template_body'])) ?></p>
                </div>
            </div>
            <div class="template-card-footer">
                <button class="btn btn-outline btn-xs" onclick='openTemplateModal(<?= json_encode($tpl) ?>)'>
                    <i class="fa-solid fa-pen"></i> Edit
                </button>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this template?')">
                    <input type="hidden" name="csrf_token" value="<?= h(generate_csrf_token()) ?>">
                    <input type="hidden" name="template_id" value="<?= $tpl['id'] ?>">
                    <button type="submit" name="delete_template" class="btn btn-danger btn-xs">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Template Modal -->
<div class="modal-overlay" id="templateModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="templateModalTitle"><i class="fa-solid fa-message"></i> New Template</h3>
            <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('modal-open')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= h(generate_csrf_token()) ?>">
            <input type="hidden" name="template_id" id="tpl_id">
            <div class="modal-body">
                <div class="form-group">
                    <label for="tpl_name">Template Name</label>
                    <input type="text" id="tpl_name" name="name" required placeholder="e.g. Critical Alert">
                </div>
                <div class="form-group">
                    <label for="tpl_alert_type">Alert Type</label>
                    <select id="tpl_alert_type" name="alert_type">
                        <option value="all">All Types</option>
                        <option value="Motion Intrusion">Motion Intrusion</option>
                        <option value="Beam Break Intrusion">Beam Break Intrusion</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tpl_body">Template Body</label>
                    <textarea id="tpl_body" name="template_body" rows="4" required placeholder="⚠ Alert: {alert_type} at {location}..."></textarea>
                </div>
                <div class="form-group checkbox-group">
                    <label class="toggle-switch">
                        <input type="checkbox" name="is_default" id="tpl_default">
                        <span class="toggle-slider"></span>
                    </label>
                    <span>Set as default for this alert type</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal-overlay').classList.remove('modal-open')">Cancel</button>
                <button type="submit" name="save_template" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save Template</button>
            </div>
        </form>
    </div>
</div>

<script>
function openTemplateModal(data) {
    const modal = document.getElementById('templateModal');
    document.getElementById('tpl_id').value = data ? data.id : '';
    document.getElementById('tpl_name').value = data ? data.name : '';
    document.getElementById('tpl_body').value = data ? data.template_body : '';
    document.getElementById('tpl_alert_type').value = data ? data.alert_type : 'all';
    document.getElementById('tpl_default').checked = data ? !!data.is_default : false;
    document.getElementById('templateModalTitle').innerHTML = data
        ? '<i class="fa-solid fa-pen"></i> Edit Template'
        : '<i class="fa-solid fa-message"></i> New Template';
    modal.classList.add('modal-open');
}
function insertVariable(v) {
    const ta = document.getElementById('tpl_body');
    if (ta) {
        const start = ta.selectionStart;
        ta.value = ta.value.substring(0, start) + v + ta.value.substring(ta.selectionEnd);
        ta.focus();
        ta.selectionStart = ta.selectionEnd = start + v.length;
    }
}
</script>

<?php include 'includes/footer.php'; ?>
