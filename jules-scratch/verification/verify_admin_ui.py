from playwright.sync_api import sync_playwright

def run(playwright):
    browser = playwright.chromium.launch()
    page = browser.new_page()

    # Navigate to the main admin page
    page.goto("http://localhost:8000/admin/index.php")

    # Navigate to the groups page
    page.get_by_role("link", name="Groups").click()
    page.wait_for_url("http://localhost:8000/admin/groups.php")

    # Navigate to the WiFi management page
    page.get_by_role("link", name="WiFi Configs").click()
    page.wait_for_url("http://localhost:8000/admin/wifi.php")

    # Take a screenshot
    page.screenshot(path="jules-scratch/verification/verification.png")

    browser.close()

with sync_playwright() as playwright:
    run(playwright)
