from playwright.sync_api import sync_playwright

def run(playwright):
    browser = playwright.chromium.launch()
    page = browser.new_page()

    # Navigate to the main admin page
    page.goto("http://localhost:8000/admin/index.php")

    # Click the first preview button
    preview_button = page.locator('.preview-btn').first
    preview_button.click()

    # Wait for the modal to appear
    page.wait_for_selector('.modal-dialog')

    # Take a screenshot
    page.screenshot(path="jules-scratch/verification/verification.png")

    browser.close()

with sync_playwright() as playwright:
    run(playwright)
