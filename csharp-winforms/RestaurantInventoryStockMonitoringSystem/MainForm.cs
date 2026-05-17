using System;
using System.Collections.Generic;
using System.Drawing;
using System.IO;
using System.Net;
using System.Text;
using System.Web.Script.Serialization;
using System.Windows.Forms;

namespace RestaurantInventoryStockMonitoringSystem
{
    public class MainForm : Form
    {
        private const string ApiBase = "http://localhost:8000/api.php";
        private DataGridView grid = new DataGridView();
        private ComboBox statusBox = new ComboBox();
        private Label messageLabel = new Label();
        private Label selectedLabel = new Label();
        private int selectedId = 0;

        public MainForm()
        {
            Text = "Restaurant Inventory Stock Monitoring System";
            Width = 1220;
            Height = 760;
            StartPosition = FormStartPosition.CenterScreen;
            MinimumSize = new Size(980, 620);
            LoadBackground();
            BuildInterface();
            Load += delegate { RefreshReports(); };
        }

        private void LoadBackground()
        {
            string bg = Path.Combine(Application.StartupPath, "assets", "restaurant_inventory_bg.png");
            if (File.Exists(bg))
            {
                BackgroundImage = Image.FromFile(bg);
                BackgroundImageLayout = ImageLayout.Stretch;
            }
            BackColor = Color.FromArgb(42, 57, 37);
        }

        private void BuildInterface()
        {
            var main = new GlassPanel();
            main.Dock = DockStyle.Fill;
            main.Padding = new Padding(22);
            Controls.Add(main);

            var title = new Label();
            title.Text = "Inventory Stock Monitoring System";
            title.Font = new Font("Segoe UI", 24, FontStyle.Bold);
            title.ForeColor = Color.White;
            title.AutoSize = true;
            title.BackColor = Color.Transparent;

            var subtitle = new Label();
            subtitle.Text = "Modern inventory request management dashboard";
            subtitle.Font = new Font("Segoe UI", 11, FontStyle.Regular);
            subtitle.ForeColor = Color.FromArgb(228, 248, 215);
            subtitle.AutoSize = true;
            subtitle.BackColor = Color.Transparent;

            var refreshButton = MakeButton("Refresh Requests");
            refreshButton.Click += delegate { RefreshReports(); };

            var updateButton = MakeButton("Update Status");
            updateButton.Click += delegate { UpdateStatus(); };

            statusBox.DropDownStyle = ComboBoxStyle.DropDownList;
            statusBox.Items.AddRange(new object[] { "Pending", "Approved", "For Purchase", "Stocked", "Rejected", "Cancelled" });
            statusBox.SelectedIndex = 0;
            statusBox.Width = 180;
            statusBox.Font = new Font("Segoe UI", 10);

            selectedLabel.Text = "Selected ID: none";
            selectedLabel.ForeColor = Color.White;
            selectedLabel.BackColor = Color.Transparent;
            selectedLabel.AutoSize = true;
            selectedLabel.Font = new Font("Segoe UI", 10, FontStyle.Bold);

            messageLabel.Text = "Start the PHP server first: php -S localhost:8000";
            messageLabel.ForeColor = Color.FromArgb(232, 250, 220);
            messageLabel.BackColor = Color.Transparent;
            messageLabel.AutoSize = true;
            messageLabel.Font = new Font("Segoe UI", 10, FontStyle.Bold);

            var topPanel = new FlowLayoutPanel();
            topPanel.Dock = DockStyle.Top;
            topPanel.Height = 165;
            topPanel.Padding = new Padding(8);
            topPanel.BackColor = Color.Transparent;
            topPanel.FlowDirection = FlowDirection.TopDown;
            topPanel.Controls.Add(title);
            topPanel.Controls.Add(subtitle);

            var controls = new FlowLayoutPanel();
            controls.BackColor = Color.Transparent;
            controls.AutoSize = true;
            controls.Padding = new Padding(0, 15, 0, 0);
            controls.Controls.Add(refreshButton);
            controls.Controls.Add(statusBox);
            controls.Controls.Add(updateButton);
            controls.Controls.Add(selectedLabel);
            controls.Controls.Add(messageLabel);
            topPanel.Controls.Add(controls);

            grid.Dock = DockStyle.Fill;
            grid.ReadOnly = true;
            grid.SelectionMode = DataGridViewSelectionMode.FullRowSelect;
            grid.AutoSizeColumnsMode = DataGridViewAutoSizeColumnsMode.Fill;
            grid.AllowUserToAddRows = false;
            grid.AllowUserToDeleteRows = false;
            grid.MultiSelect = false;
            grid.BackgroundColor = Color.FromArgb(247, 250, 242);
            grid.GridColor = Color.FromArgb(190, 205, 190);
            grid.DefaultCellStyle.BackColor = Color.FromArgb(252, 255, 247);
            grid.DefaultCellStyle.ForeColor = Color.FromArgb(30, 55, 25);
            grid.DefaultCellStyle.SelectionBackColor = Color.FromArgb(89, 132, 65);
            grid.DefaultCellStyle.SelectionForeColor = Color.White;
            grid.ColumnHeadersDefaultCellStyle.BackColor = Color.FromArgb(54, 86, 43);
            grid.ColumnHeadersDefaultCellStyle.ForeColor = Color.White;
            grid.EnableHeadersVisualStyles = false;
            grid.Font = new Font("Segoe UI", 9);
            grid.CellClick += Grid_CellClick;

            main.Controls.Add(grid);
            main.Controls.Add(topPanel);
        }

        private Button MakeButton(string text)
        {
            var b = new Button();
            b.Text = text;
            b.Width = 150;
            b.Height = 36;
            b.FlatStyle = FlatStyle.Flat;
            b.BackColor = Color.FromArgb(150, 204, 98);
            b.ForeColor = Color.FromArgb(20, 45, 15);
            b.Font = new Font("Segoe UI", 10, FontStyle.Bold);
            return b;
        }

        private void RefreshReports()
        {
            try
            {
                string json = Get(ApiBase + "?action=list");
                var serializer = new JavaScriptSerializer();
                var response = serializer.Deserialize<ApiListResponse>(json);
                if (response == null || !response.success)
                {
                    messageLabel.Text = "API returned an error.";
                    return;
                }
                grid.DataSource = response.data;
                selectedId = 0;
                selectedLabel.Text = "Selected ID: none";
                messageLabel.Text = "Loaded " + response.data.Count + " request(s) from PHP API.";
            }
            catch (Exception ex)
            {
                messageLabel.Text = "Connection error: " + ex.Message;
                MessageBox.Show("Cannot connect to PHP API.\n\nMake sure this is running in the php-api folder:\nphp -S localhost:8000\n\nDetails: " + ex.Message, "API Connection Error", MessageBoxButtons.OK, MessageBoxIcon.Warning);
            }
        }

        private void UpdateStatus()
        {
            if (selectedId <= 0)
            {
                MessageBox.Show("Please select a request first.");
                return;
            }
            try
            {
                string status = statusBox.SelectedItem.ToString();
                string postData = "action=update_status&id=" + Uri.EscapeDataString(selectedId.ToString()) + "&status=" + Uri.EscapeDataString(status);
                string json = Post(ApiBase, postData);
                var serializer = new JavaScriptSerializer();
                var response = serializer.Deserialize<ApiSimpleResponse>(json);
                if (response != null && response.success)
                {
                    messageLabel.Text = "Request #" + selectedId + " updated to " + status + ".";
                    RefreshReports();
                }
                else
                {
                    MessageBox.Show(response != null ? response.message : "Update failed.");
                }
            }
            catch (Exception ex)
            {
                MessageBox.Show("Update failed: " + ex.Message);
            }
        }

        private void Grid_CellClick(object sender, DataGridViewCellEventArgs e)
        {
            if (e.RowIndex < 0 || grid.Rows.Count == 0) return;
            var row = grid.Rows[e.RowIndex];
            if (row.Cells["id"].Value != null)
            {
                selectedId = Convert.ToInt32(row.Cells["id"].Value);
                selectedLabel.Text = "Selected ID: " + selectedId;
                if (row.Cells["status"].Value != null)
                {
                    string status = row.Cells["status"].Value.ToString();
                    if (statusBox.Items.Contains(status)) statusBox.SelectedItem = status;
                }
            }
        }

        private string Get(string url)
        {
            using (var client = new WebClient())
            {
                client.Encoding = Encoding.UTF8;
                return client.DownloadString(url);
            }
        }

        private string Post(string url, string postData)
        {
            byte[] bytes = Encoding.UTF8.GetBytes(postData);
            var request = (HttpWebRequest)WebRequest.Create(url);
            request.Method = "POST";
            request.ContentType = "application/x-www-form-urlencoded";
            request.ContentLength = bytes.Length;
            using (var stream = request.GetRequestStream()) stream.Write(bytes, 0, bytes.Length);
            using (var response = (HttpWebResponse)request.GetResponse())
            using (var reader = new StreamReader(response.GetResponseStream())) return reader.ReadToEnd();
        }
    }

    public class GlassPanel : Panel
    {
        protected override void OnPaintBackground(PaintEventArgs e)
        {
            base.OnPaintBackground(e);
            using (var brush = new SolidBrush(Color.FromArgb(145, 12, 28, 13)))
            {
                e.Graphics.FillRectangle(brush, ClientRectangle);
            }
        }
    }

    public class InventoryRequest
    {
        public int id { get; set; }
        public string ingredient_name { get; set; }
        public string category { get; set; }
        public string quantity { get; set; }
        public string unit { get; set; }
        public string requested_by { get; set; }
        public string branch_area { get; set; }
        public string priority { get; set; }
        public string remarks { get; set; }
        public string status { get; set; }
        public string created_at { get; set; }
        public string updated_at { get; set; }
    }

    public class ApiListResponse
    {
        public bool success { get; set; }
        public List<InventoryRequest> data { get; set; }
    }

    public class ApiSimpleResponse
    {
        public bool success { get; set; }
        public string message { get; set; }
    }
}
