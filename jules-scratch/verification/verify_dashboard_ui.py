from playwright.sync_api import sync_playwright

def run(playwright):
    browser = playwright.chromium.launch()
    page = browser.new_page()

    # Navigate to the dashboard page
    page.goto("http://localhost:8000/admin/dashboard.php")

    # Wait for the page to load
    page.wait_for_selector('.card')

    # Take a screenshot
    page.screenshot(path="jules-scratch/verification/verification.png")

    browser.close()

with sync_playwright() as playwright:
    run(playwright)
